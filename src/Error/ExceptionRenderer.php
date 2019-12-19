<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\Error;

use Cake\Error\ExceptionRenderer as CakeExceptionRenderer;
use Cake\Log\LogTrait;

/**
 * Custom exception renderer class.
 * Handle with templates 500 and 400 (for status code < 500).
 */
class ExceptionRenderer extends CakeExceptionRenderer
{
    use LogTrait;

    /**
     * {@inheritDoc}
     */
    protected function _template(\Exception $exception, $method, $code): string
    {
        $exception = $this->_unwrap($exception);

        $template = 'error500';
        if ($code < 500) {
            $template = 'error400';
        }

        return $this->template = $template;
    }

    /**
     * {@inheritDoc}
     */
    protected function _outputMessageSafe($template)
    {
        $builder = $this->controller->viewBuilder();
        $builder->setLayoutPath('')
            ->setTemplatePath('Error');

        // first try to use AppView class. Fallback to internal template on failure
        try {
            $view = $this->controller->createView();
            $body = $view->render($template, 'error');
        } catch (\Exception $e) {
            // first log the new exception to trace the new error too.
            $this->log($e->getMessage());

            $helpers = ['Form', 'Html'];
            $builder->setHelpers($helpers, false);

            $view = $this->controller->createView('View');
            $body = $view->render('BEdita/WebTools.' . $template, 'BEdita/WebTools.error');
        }

        return $this->controller->response->withStringBody($body)->withType('html');
    }
}
