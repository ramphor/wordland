<div class="wordland-agent-wraper">
    <div class="wordland-agent-header">
        <h1 class="wl-agent-name"><?php echo $agent_name; ?></h1>

        <div class="agent-heading-info">
            <div class="heading-infos">
                <div class="agent-position">sales executive</div>
                <div class="agent-email"><?php echo $agent_email; ?></div>
            </div>
        </div>
    </div>

    <div class="wordland-agent-content">
        <?php wordland_template('agent/parts/main', $agent_data); ?>
        <?php wordland_template('agent/parts/tags', $agent_data); ?>
        <?php wordland_template('agent/parts/metas', $agent_data); ?>
        <?php wordland_template('agent/parts/biography', $agent_data); ?>
    </div>
</div>
