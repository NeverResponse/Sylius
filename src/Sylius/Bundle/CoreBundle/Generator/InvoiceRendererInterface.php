<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Generator;

/**
 * @author Jan Góralski <jan.goralski@lakion.com>
 */
interface InvoiceRendererInterface
{
    /**
     * @param string $template
     * @param array $parameters
     *
     * @return string
     */
    public function render($template, array $parameters);
}
