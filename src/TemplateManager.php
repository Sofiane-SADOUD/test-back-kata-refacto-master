<?php

namespace Template;

use Template\Entity\Quote;
use Template\Entity\Template;
use Template\Context\ApplicationContext;
use Template\Repository\QuoteRepository;
use Template\Repository\SiteRepository;
use Template\Repository\DestinationRepository;
use Template\Entity\User;

class TemplateManager
{
    /**
     * Get populated $template with $data
     * @param Template $template
     * @param array $data
     * @throws \RuntimeException
     * @return \Template\Entity\Template
     */
    public function getTemplateComputed(Template $template, array $data)
    {
        if (!$template) {
            throw new \RuntimeException('no template given');
        }

        $replacedTemplate = clone($template);
        $replacedTemplate->subject = $this->replacePatternsWithData($replacedTemplate->subject, $data);
        $replacedTemplate->content = $this->replacePatternsWithData($replacedTemplate->content, $data);

        return $replacedTemplate;
    }

    /**
     * Replacing patterns in given $text by $data
     * @param string $text
     * @param array $data
     * @return string
     */
    private function replacePatternsWithData($text, array $data)
    {
        if (isset($data['quote']) && $data['quote'] instanceof Quote) {
            $text = $this->replaceQuotePatterns($data['quote'], $text);
        }

        $user  = (isset($data['user'])  && ($data['user']  instanceof User)) ? $data['user'] : ApplicationContext::getInstance()->getCurrentUser();

        if ($user) {
            $text = $this->replaceUserPatterns($user, $text);
        }

        return $text;
    }

    /**
     * Replacing patterns in $text with $quote data
     * @param Quote $quote
     * @param string $text
     * @return string
     */
    private function replaceQuotePatterns(Quote $quote, $text)
    {
        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        $siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationFromRepository = DestinationRepository::getInstance()->getById($quote->destinationId);

        // Replace quote:destination_link
        if (strpos($text, '[quote:destination_link]') !== false) {
            if ($destinationFromRepository) {
                $text = str_replace('[quote:destination_link]', $siteFromRepository->url . '/' . $destinationFromRepository->countryName . '/quote/' . $quoteFromRepository->id, $text);
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }
        }

        // Replace quote:summary_html
        if (strpos($text, '[quote:summary_html]') !== false) {
            $text = str_replace('[quote:summary_html]', Quote::renderHtml($quoteFromRepository), $text);
        }

        // Replace quote:summary
        if (strpos($text, '[quote:summary]')!== false) {
            $text = str_replace('[quote:summary]', Quote::renderText($quoteFromRepository), $text);
        }

        // Replace quote:destination_name
        if (strpos($text, '[quote:destination_name]') !== false) {
            $countryName = !empty($destinationFromRepository->countryName) ? $destinationFromRepository->countryName : '';
            $text = str_replace('[quote:destination_name]', $countryName, $text);
        }

        return $text;
    }

    /**
     * Replacing patterns in $text with $user data
     * @param User $user
     * @param string $text
     * @return string
     */
    private function replaceUserPatterns(User $user, $text)
    {
        // replace user first_name
        if (strpos($text, '[user:first_name]') !== false) {
            $firstname = !empty($user->firstname) ? ucfirst(mb_strtolower($user->firstname)) : '';
            $text = str_replace('[user:first_name]', $firstname, $text);
        }

        // replace user last_name
        if (strpos($text, '[user:last_name]') !== false) {
            $lastname = !empty($user->lastname) ? mb_strtoupper($user->lastname) : '';
            $text = str_replace('[user:last_name]', $lastname, $text);
        }

        return $text;
    }
}
