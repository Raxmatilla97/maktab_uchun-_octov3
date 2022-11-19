<?php namespace Backend\Behaviors\FormController;

use Site;

/**
 * HasMultisite contains logic for managing multisite records
 */
trait HasMultisite
{
    /**
     * formHasMultisite
     */
    public function formHasMultisite($model)
    {
        return $model &&
            $model->isClassInstanceOf(\October\Contracts\Database\MultisiteInterface::class) &&
            $model->isMultisiteEnabled();
    }

    /**
     * makeMultisiteRedirect
     */
    public function makeMultisiteRedirect($context = null, $model = null)
    {
        if (!$model) {
            return;
        }

        if (!$this->controller->formHasMultisite($model)) {
            return;
        }

        $activeSiteId = Site::getSiteIdFromContext();
        if ((int) $model->site_id === (int) $activeSiteId) {
            return;
        }

        $otherModel = $model->findOrCreateForSite($activeSiteId);

        return $this->makeRedirect($context, $otherModel, ['_site_id' => $activeSiteId]);
    }

    /**
     * onSwitchSite
     */
    public function onSwitchSite($recordId = null)
    {
        $result = [];
        $model = $this->controller->formFindModelObject($recordId);

        if ($model && !$model->findForSite(post('site_id'))) {
            $result['confirm'] = __('A record does not exist for the selected site. Create one?');
        }

        return $result;
    }

    /**
     * addHandlerToSiteSwitcher
     */
    protected function addHandlerToSiteSwitcher()
    {
        $siteSwitcher = $this->getWidget('siteSwitcher');
        if (!$siteSwitcher) {
            return;
        }

        $siteSwitcher->setSwitchHandler('onSwitchSite');
    }
}
