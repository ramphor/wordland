<?php
namespace WordLand\Frontend\Dashboard\Section;

use Ramphor\User\Abstracts\SectionAbstract;
use WordLand\Template;

class UserDetailsSection extends SectionAbstract
{
    public function getName()
    {
        return 'user_details';
    }

    public function getContent()
    {
        return Template::render('agent/my-profile/sections/user-details', array(
            'heading_text' => $this->heading,
            'description' => $this->description,
        ), null, false);
    }
}
