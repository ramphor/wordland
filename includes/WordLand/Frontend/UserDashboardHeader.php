<?php
namespace WordLand\Frontend;

class UserDashboardHeader
{
    public function init()
    {
        add_action('wordland_before_feature_content', array($this, 'renderHeader'));
    }

    public function renderHeader()
    {
        ?>
        <div>User dashboard header</div>
        <?php
    }
}
