<div class="ninja-deactivation-feedback <?php echo esc_attr($slug); ?>_modal no-confirmation-message">
    <div class="ninja-modal-dialog">
        <div class="ninja-modal-header">
            <h3 class="nt-deactivation-title">Quick feedback</h3>
        </div>
        <div class="ninja-modal-body">
            <div class="ninja-modal-panel" data-panel-id="confirm"><p></p></div>
            <div class="ninja-modal-panel active" data-panel-id="reasons">
                <h3><strong>If you have a moment, please let us know why you are deactivating:</strong></h3>
                <ul id="reasons-list">
                    <?php foreach ($reasons as $reason_key => $reason): ?>
                        <li class="reason">
                            <label>
                            <span>
                                <input class="<?php echo $reason['has_custom'] ? 'has_custom nt-radio' : 'nt-radio'; ?>" type="radio"
                                       name="selected-reason" value="<?php echo esc_attr($reason_key); ?>">
                            </span>
                                <span><?php echo esc_attr($reason['label']); ?></span>
                            </label>
                            <?php if ($reason['has_custom']): ?>
                                <div class="ninja_custom_feedback">
                                    <label>
                                        <span><?php echo esc_attr($reason['custom_label']); ?></span>
                                        <input type="text" name="<?php echo esc_attr($reason_key); ?>_custom"
                                               placeholder="<?php echo esc_attr($reason['custom_placeholder']); ?>"/>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="ninja-modal-footer">
            <a class="ninja_action_deactivate button" href="#">Skip & Deactivate</a>
            <a href="#" class="ninja_action_close button button-primary button-close">Cancel</a>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        jQuery('tr[data-slug="<?php echo esc_attr($slug);?>"]').on('click', '.deactivate a', function (e) {
            e.preventDefault();
            $('.<?php echo esc_attr($slug); ?>_modal').addClass('active');
            $('.<?php echo esc_attr($slug); ?>_modal a.ninja_action_deactivate').attr('href', $(this).attr('href'));
        });

        $('.ninja_action_close').on('click', function (e) {
            e.preventDefault();
            $('.ninja-deactivation-feedback').removeClass('active');
        });

        $('.<?php echo esc_attr($slug); ?>_modal input[name="selected-reason"').on('change', function (e) {
            e.preventDefault();
            $('a.ninja_action_deactivate').text('Submit & Deactive').addClass('has_feedback');
            $('.ninja_custom_feedback').removeClass('active');
            $(this).closest('.reason').find('.ninja_custom_feedback').addClass('active');
        });

        $('.<?php echo esc_attr($slug); ?>_modal .ninja-modal-footer').on('click', 'a.ninja_action_deactivate.has_feedback', function (e) {
            e.preventDefault();
            var redirectLink = $(this).attr('href');

            var reason = $('input[name="selected-reason"]:checked').val();
            var custom_message = $('input[name="' + reason + '_custom"]').val();
            $(this).text('Deactivating...').attr('disabled', true);
            jQuery.post(ajaxurl, {
                action: '<?php echo esc_attr($slug); ?>_deactivate_feedback',
                reason: reason,
                custom_message: custom_message
            })
                .then(function (response) {

                })
                .always(function () {
                    window.location.href = redirectLink;
                });
        });
    });
</script>

<style type="text/css">
    .ninja-deactivation-feedback {
        position: fixed;
        overflow: auto;
        height: 100%;
        width: 100%;
        top: 0;
        z-index: 100000;
        display: none;
        background: rgba(0, 0, 0, 0.6);
    }

    .ninja-deactivation-feedback.active {
        display: block;
    }

    .ninja-modal-dialog {
        position: absolute;
        left: 50%;
        margin-left: -298px;
        padding-bottom: 30px;
        top: 15%;
        z-index: 100001;
        width: 600px;
        border-radius: 12px;
    }

    .ninja-modal-header {
        border-bottom: #eeeeee solid 1px;
        background: #ffffff;
        color: #0E121B !important;
        padding: 20px;
        position: relative;
        margin-bottom: -10px;
        border-radius: 12px 12px 0 0;
    }

    .nt-deactivation-title {
        color: #0E121B;
        font-size: 20px;
        font-style: normal;
        font-weight: 500;
        line-height: 28px;
        text-transform: uppercase;
        margin: 0;
    }

    .ninja-modal-body {
        border: 0;
        background: #fefefe;
        padding: 10px 20px 20px 20px;
    }

    .ninja-modal-footer {
        border: 0;
        background: #fefefe;
        padding: 20px;
        border-top: #eeeeee solid 1px;
        text-align: right;
        border-radius: 0 0 12px 12px;
    }

    .ninja_custom_feedback {
        display: none;
    }

    .ninja_custom_feedback.active {
        display: block;
    }

    .ninja_custom_feedback.active label {
        display: block;
        margin-top: 10px;
        margin-bottom: 4px;
    }

    .ninja_custom_feedback.active label span {
        font-weight: 500;
        display: block;
        width: 100%;
    }

    .ninja_custom_feedback.active input {
        display: block;
        margin-top: 0;
        margin-bottom: 15px;
        width: 100%;
        padding: 5px 10px;
        border-radius: 12px;
        border: 1px solid #335cff;
    }

    .reason {
        margin-bottom: 10px;
    }
    .nt-radio {
        border-color: #335cff !important;
        box-shadow: 0 0 0 1px #335cff !important;
    }
    .nt-radio:before {
        background-color: #335cff !important;
    }

    .ninja-modal-footer .ninja_action_deactivate {
        background: #FFFFFF;
        color: #525866;
        border: 1px solid #E1E4EA;
        border-radius: 8px;
        padding: 0 12px ;
        font-size: 14px;
        font-weight: 500;
    }
    .ninja-modal-footer .ninja_action_deactivate:hover {
        background: #F5F6F7 !important;
        color: #525866 !important;
        border: 1px solid #E1E4EA !important;
    }

    .ninja-modal-footer .ninja_action_close {
        background: #335cff;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        font-weight: 500;
    }
    .ninja-modal-footer .ninja_action_close:hover {
        background: #2547D0 !important;
        color: #FFFFFF !important;
        border: none;
    }

</style>
