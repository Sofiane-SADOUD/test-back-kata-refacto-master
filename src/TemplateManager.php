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
    public function getTemplateComputed(Template $template, array $data)
    {
        if (!$template) {
            throw new \RuntimeException('no template given');
        }

        $replacedTemplate = clone($template);
        $replacedTemplate->subject = $this->computeText($replacedTemplate->subject, $data);
        $replacedTemplate->content = $this->computeText($replacedTemplate->content, $data);

        return $replacedTemplate;
    }

    private function computeText($text, array $data)
    {

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote) {
            $text = $this->computeQuote($quote, $text);
        }


        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : ApplicationContext::getInstance()->getCurrentUser();

        if($user) {
            $text = $this->computeUser($user, $text);
        }

        return $text;
    }

    private function computeQuote(Quote $quote, $text)
    {
        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        $siteFromRepository = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationFromRepository = DestinationRepository::getInstance()->getById($quote->destinationId);

        // Replace quote:destination_link
        if(strpos($text, '[quote:destination_link]') !== false) {
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

            if ($destination) {
                $text = str_replace('[quote:destination_link]', $siteFromRepository->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id, $text);
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
            $text = str_replace('[quote:destination_name]', $destinationFromRepository->countryName, $text);
        }

        return $text;
    }

    private function computeUser(User $user, $text)
    {
        // replace user info
        if (strpos($text, '[user:first_name]') !== false) {
            $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }
}
