<style>
    .sortable-list {
        background-color: #FFFFFF;
        list-style: none;
        margin: 0;
        min-height: 60px;
        padding: 5px;
    }
    .sortable-item {
        background-color: #FFF;
        border: 1px solid #000;
        cursor: move;
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        padding: 5px 0;
        text-align: left;
    }
</style>
<div class="container-responsive">
    <div class="row">
        <div class="col-md-2">
            <ul class="sortable-list list-group">
            <?php
            query_posts( array ( 'post_type' => 'workout', 'posts_per_page' => -1, 'order' => 'ASC' ) );
            while (have_posts()) : the_post();
                ?>
                <li workout_type="<?php echo get_post_meta( get_the_ID(), 'workout_type', true ) ?>" workout_distance="<?php echo get_post_meta( get_the_ID(), 'workout_distance', true ) ?>" workout_duration="<?php echo get_post_meta( get_the_ID(), 'workout_duration', true ) ?>" class="workout sortable-item list-group-item list-group-item-success" id="workout<?php echo get_the_ID() ?>"><?php echo get_the_title() ?></li>
            <?php
            endwhile;
            ?>
            <?php
            query_posts( array ( 'post_type' => 'workout_schedule', 'posts_per_page' => -1, 'order' => 'ASC' ) );
            while (have_posts()) : the_post();
                ?>
                <li workout_days='<?php echo get_post_meta( get_the_ID(), 'workout_days', true ) ?>' workout_id="<?php echo get_post_meta( get_the_ID(), 'workout_id', true ) ?>" class="workout sortable-item list-group-item list-group-item-info" id="workout<?php echo get_the_ID() ?>"><?php echo get_the_title() ?></li>
            <?php
            endwhile;
            ?>
            </ul>
        </div>
        <div class="col-md-10">
            <table class="table table-bordered">
                <tr>
                    <th colspan="7"> <?php echo $title ?> <?php echo $year ?> </th>
                </tr>
                <tr>
                    <td style="width:15%">S</td>
                    <td style="width:14%">M</td>
                    <td style="width:14%">T</td>
                    <td style="width:14%">W</td>
                    <td style="width:14%">T</td>
                    <td style="width:14%">F</td>
                    <td style="width:15%">S</td>
                </tr>
                <?php $dayCount = 1; ?>
                <tr>
                    <?php while ( $blank > 0 ) { ?>
                        <td></td>
                        <?php $blank = $blank-1; ?>
                        <?php $dayCount++; ?>
                    <?php } ?>
                    <?php $dayNum = 1; ?>
                    <?php while ( $dayNum <= $daysInMonth ) { ?>
                        <td nowrap class="dropzone">
                            <ul id="target_<?php echo $dayNum ?>" day="<?php echo $dayNum ?>" class="sortable-list"></ul>
                            <?php echo $dayNum ?>
                        </td>
                        <?php $dayNum++; ?>
                        <?php $dayCount++; ?>
                        <?php if ($dayCount > 7) { ?>
                            </tr>
                            <tr>
                            <?php $dayCount = 1; ?>
                        <?php } ?>
                    <?php } ?>
                    <?php while ( $dayCount >1 && $dayCount <=7 ) { ?>
                        <td> </td>
                        <?php $dayCount++; ?>
                    <?php } ?>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modalMessage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="messageLabel">Workout Information</h4>
            </div>
            <div class="modal-body" id="messageContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';

</script>
<script>
    jQuery('.workout').live("click", function() {
        var type = jQuery(this).attr('workout_type');
        var distance = jQuery(this).attr('workout_distance');
        var duration = jQuery(this).attr('workout_duration');
        var html = 'Type: ' + type + '<br />';
        html += 'Distance: ' + distance + '<br />';
        html += 'Duration: ' + duration + '<br />';
        jQuery('#messageContent').html(html);
        jQuery('#modalMessage').modal({
            backdrop: 'static',
            keyboard: false
        });
    });
    jQuery('.sortable-list').sortable({
        connectWith: '.sortable-list',
        placeholder: 'placeholder',
        stop: function( event, ui ) {
            var workoutId = jQuery('#'+jQuery(ui.item).attr('id')).attr('workout_id');
            var workoutDays = jQuery('#'+jQuery(ui.item).attr('id')).attr('workout_days');
            var targetDay = jQuery('#'+jQuery(ui.item).attr('id')).parent().attr('day');
            var days = JSON.parse(workoutDays);
            if (workoutId) {
                jQuery(ui.sender).sortable('cancel');
                var data = {
                    action: 'workoutlookup',
                    workout_id: workoutId
                }
                jQuery.post(ajaxurl, data, function(data) {
                    var response = JSON.parse(data);
                    if (response.error) {
                        alert(response.error);
                    } else {
                        for (var i=0; i<days.length; i++) {
                            var target = 'target_' + (parseInt(days[i]) + parseInt(targetDay) - 1);
                            jQuery('#' + target).html('<li workout_type="' + response.workout_type + '" workout_distance="' + response.workout_distance + '" workout_duration="' + response.workout_duration + '" class="workout sortable-item list-group-item list-group-item-success" id="workout' + response.workout_id + '">' + response.workout_title + '</li>');
                        }
                    }
                })
            }
        }
    });
</script>