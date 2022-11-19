<?php namespace Tailor\Components;

use Event;
use Tailor\Classes\ComponentVariable;
use Tailor\Classes\BlueprintIndexer;
use Tailor\Models\EntryRecord;
use Tailor\Models\PreviewToken;
use Cms\Classes\ComponentModuleBase;

/**
 * SectionComponent displays a list of records.
 */
class SectionComponent extends ComponentModuleBase
{
    /**
     * @var object primaryRecordCache
     */
    protected $primaryRecordCache;

    /**
     * @var array otherSiteCache
     */
    protected $otherSiteCache;

    /**
     * @var bool multisiteCache
     */
    protected $multisiteCache;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'Section',
            'description' => 'Defines a website section with a supporting entry.'
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'handle' => [
                'title' => 'Handle',
                'type' => 'dropdown',
                'showExternalParam' => false
            ],
            'entrySlug' => [
                'title' => 'Slug',
                'type' => 'string',
                'description' => 'Specify the slug used to find the primary record.'
            ],
            'entryDefault' => [
                'title' => 'Default View',
                'type' => 'checkbox',
                'description' => 'Used as default entry point when previewing the record.',
            ],
            'fullSlug' => [
                'title' => 'Full Slug',
                'type' => 'checkbox',
                'description' => 'Use the full slug when looking up the record.',
            ],
        ];
    }

    /**
     * makePrimaryAccessor returns the PHP object variable for the Twig view layer.
     */
    public function makePrimaryAccessor()
    {
        return new ComponentVariable($this);
    }

    /**
     * init
     */
    public function init()
    {
        Event::listen('cms.sitePicker.overrideParams', function($page, $params, $currentSite, $proposedSite) {
            return $this->handleMultisiteParams($proposedSite, $params);
        });
    }

    /**
     * onRun
     */
    public function onRun()
    {
        if ($token = get('_preview_token')) {
            PreviewToken::checkTokenForCurrentUrl($token);
        }
    }

    /**
     * getHandleOptions
     */
    public function getHandleOptions()
    {
        $blueprints = BlueprintIndexer::instance()->listSections();

        $result = [];
        foreach ($blueprints as $bp) {
            $result[$bp->handle] = $bp->name . ' ('.$bp->handle.')';
        }

        return $result;
    }

    /**
     * getPrimaryRecord
     */
    public function getPrimaryRecord()
    {
        if ($this->primaryRecordCache !== null) {
            return $this->primaryRecordCache;
        }

        $query = $this->getPrimaryRecordQuery();

        // Using entry point
        if ($slug = $this->property('entrySlug')) {
            if ($model = $this->getPreviewModel($query)) {
                return $model;
            }

            if ($this->property('fullSlug')) {
                return $query->where('fullslug', $slug)->first();
            }

            return $query->where('slug', $slug)->first();
        }

        // Single section is the same as an entry point
        if ($query->getModel()->isEntrySingle()) {
            if ($model = $this->getPreviewModel($query)) {
                return $model;
            }

            return $query->first();
        }

        // Assuming multiple records are wanted
        if ($query->getModel()->isEntryStructure()) {
            return $query->getNested();
        }

        return $this->primaryRecordCache = $query->get();
    }

    /**
     * getPrimaryRecordQuery
     */
    public function getPrimaryRecordQuery()
    {
        $handle = $this->property('handle');

        $model = EntryRecord::inSection($handle)->applyVisibleFrontend();

        return $model;
    }

    /**
     * getPreviewModel
     */
    protected function getPreviewModel($query)
    {
        if (
            ($token = PreviewToken::getEnabledToken()) &&
            ($previewId = $token->getRouteParam('id'))
        ) {
            return $query->withoutGlobalScopes()->find($previewId);
        }
    }

    /**
     * handleMultisiteParams is for multisite
     */
    protected function handleMultisiteParams($site, $params)
    {
        if (!$this->isMultisiteEnabled()) {
            return;
        }

        $otherRecord = $this->findOtherSiteRecords()->where('site_id', $site->id)->first();
        $slugName = $this->paramName('entrySlug');
        if ($otherRecord && $slugName) {
            $params[$slugName] = $this->property('fullSlug') ? $otherRecord->fullslug : $otherRecord->slug;
            return $params;
        }
    }

    /**
     * findOtherSiteRecords is for multisite
     */
    protected function findOtherSiteRecords()
    {
        if ($this->otherSiteCache !== null) {
            return $this->otherSiteCache;
        }

        $primaryRecord = $this->getPrimaryRecord();
        $otherRecords = $primaryRecord->newOtherSiteQuery()->get();

        return $this->otherSiteCache = $otherRecords;
    }

    /**
     * isMultisiteEnabled
     */
    protected function isMultisiteEnabled()
    {
        if ($this->multisiteCache !== null) {
            return $this->multisiteCache;
        }

        $primaryRecord = $this->getPrimaryRecord();

        return $this->multisiteCache = $primaryRecord &&
            $primaryRecord->isMultisiteEnabled();
    }
}
