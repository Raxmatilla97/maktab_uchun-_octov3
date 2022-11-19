<?php namespace Tailor\ContentFields;

use Tailor\Models\EntryRecord;
use Tailor\Classes\BlueprintIndexer;
use Tailor\Classes\CustomMultiJoinRelation;
use October\Contracts\Element\FormElement;
use October\Contracts\Element\ListElement;
use October\Contracts\Element\FilterElement;
use SystemException;

/**
 * EntriesField allows association to entries
 *
 * @package october\tailor
 * @author Alexey Bobkov, Samuel Georges
 */
class EntriesField extends FallbackField
{
    /**
     * @var string source UUID of the section
     */
    public $source;

    /**
     * @var int|null maxItems allowed
     */
    public $maxItems;

    /**
     * @var string displayMode for the relationship
     */
    public $displayMode;

    /**
     * @var mixed sourceCache of the source blueprint
     */
    protected $sourceCache;

    /**
     * defineConfig will process the field configuration.
     */
    public function defineConfig(array $config)
    {
        if (isset($config['source'])) {
            $this->source = (string) $config['source'];
        }

        if (isset($config['maxItems'])) {
            $this->maxItems = (int) $config['maxItems'];
        }

        if (isset($config['displayMode'])) {
            $this->displayMode = (string) $config['displayMode'];
        }
        else {
            $this->displayMode = 'recordfinder';
        }
    }

    /**
     * defineFormField will define how a field is displayed in a form.
     */
    public function defineFormField(FormElement $form, $context = null)
    {
        $field = $form->addFormField($this->fieldName, $this->label);

        $config = $this->config + ['nameFrom' => 'title'];

        $field->useConfig($config);

        if ($this->displayMode === 'recordfinder' && $this->maxItems === 1) {
            $field->displayAs('recordfinder');
        }
        else {
            $field->displayAs('relation');
        }
    }

    /**
     * defineListColumn
     */
    public function defineListColumn(ListElement $list, $context = null)
    {
        $partial = $this->maxItems === 1 ? 'column_single' : 'column_multi';

        $list->defineColumn($this->fieldName, $this->label)->displayAs('partial')
            ->path("~/modules/tailor/contentfields/entriesfield/partials/_{$partial}.php")
            ->clickable(false)->sortable(false)->useConfig($this->column ?: []);
    }

    /**
     * defineFilterScope
     */
    public function defineFilterScope(FilterElement $filter, $context = null)
    {
        $filter->defineScope($this->fieldName, $this->label)
            ->displayAs('group')
            ->nameFrom('title')
            ->useConfig($this->scope ?: [])
        ;
    }

    /**
     * extendModelObject will extend the record model.
     */
    public function extendModelObject($model)
    {
        $useMultisite = $this->getSourceBlueprint()->useMultisite();

        // Define the relationship
        if ($this->maxItems === 1) {
            $model->belongsTo[$this->fieldName] = [
                EntryRecord::class,
                'key' => $this->getSingularKeyName(),
                'otherKey' => $useMultisite ? 'site_root_id' : 'id'
            ];
        }
        else {
            $model->belongsToMany[$this->fieldName] = [
                EntryRecord::class,
                'table' => $model->getBlueprintDefinition()->getJoinTableName(),
                'relationClass' => CustomMultiJoinRelation::class,
                'relatedKey' => $useMultisite ? 'site_root_id' : 'id'
            ];
        }

        // For defining list columns and form fields
        $model->bindEvent('model.afterRelation', function($name, $related) {
            if ($name === $this->fieldName) {
                $related->extendWithBlueprint($this->getSourceBlueprint()->uuid);
            }
        });
    }

    /**
     * getSourceBlueprint validates and converts source to a blueprint
     */
    protected function getSourceBlueprint()
    {
        if ($this->sourceCache !== null) {
            return $this->sourceCache;
        }

        if (!$this->source) {
            throw new SystemException("Missing source for '{$this->fieldName}'.");
        }

        $indexer = BlueprintIndexer::instance();

        $uuid = $indexer->hasSection($this->source);
        if (!$uuid) {
            throw new SystemException("Invalid source '{$this->source}' for '{$this->fieldName}'.");
        }

        return $this->sourceCache = BlueprintIndexer::instance()->findSection($uuid);
    }

    /**
     * extendDatabaseTable
     */
    public function extendDatabaseTable($table)
    {
        if ($this->maxItems === 1) {
            $table->integer($this->getSingularKeyName())->unsigned()->nullable();
        }
    }

    /**
     * getSingularKeyName
     */
    protected function getSingularKeyName()
    {
        return $this->fieldName.'_id';
    }
}
