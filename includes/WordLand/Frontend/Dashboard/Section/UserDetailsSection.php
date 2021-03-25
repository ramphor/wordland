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
            'value' => get_user_meta(get_current_user_id(), 'wordland_agent_postion', true),
        ), null, false);
    }

    public function save()
    {
        if (!is_user_logged_in()) {
            return false;
        }
        if (isset($_POST['agent_postion'])) {
            update_user_meta(get_current_user_id(), 'wordland_agent_postion', $_POST['agent_postion']);
        }
    }
}
