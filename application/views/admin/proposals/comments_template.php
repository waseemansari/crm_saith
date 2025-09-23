<?php defined('BASEPATH') or exit('No direct script access allowed');
ob_start();


// Step 1: Collect all proposal IDs that already have a final file
$final_proposals = [];
foreach ($comments as $comment) {
    $files = $this->db->get_where('tblproposal_comments_file', ['comments_id' => $comment['id']])->result_array();
    foreach ($files as $f) {
        if ($f['is_final'] == 1) {
            $final_proposals[$f['proposal_id']] = true;
        }
    }
}

$len = count($comments);
$i   = 0;
foreach ($comments as $comment) { 
        $sql = "SELECT * FROM tblproposal_comments_file WHERE comments_id = ? ";
        $files =  $this->db->query($sql, [$comment['id']])->result_array();
        
    ?>
<div class="col-md-12 comment-item"
    data-commentid="<?= e($comment['id']); ?>">
    <?php if ($comment['staffid'] != 0) { ?>
    <a
        href="<?= admin_url('profile/' . $comment['staffid']); ?>">
        <?= staff_profile_image($comment['staffid'], [
            'staff-profile-image-small',
            'media-object img-circle pull-left mright10',
        ]);
        ?>
    </a>
    <?php } ?>
    <?php if ($comment['staffid'] == get_staff_user_id() || is_admin()) { ?>
    <a href="#" class="pull-right"
        onclick="remove_proposal_comment(<?= e($comment['id']); ?>); return false;">
        <i class="fa fa-times text-danger"></i>
        <a href="#" class="pull-right mright5"
            onclick="toggle_proposal_comment_edit(<?= e($comment['id']); ?>);return false;">
            <i class="fa-regular fa-pen-to-square"></i>
        </a>
    </a>
    <?php } ?>
    <div class="media-body">
        <div class="mtop5">
            <?php if ($comment['staffid'] != 0) { ?>
            <a
                href="<?= admin_url('profile/' . $comment['staffid']); ?>">
                <?= e(get_staff_full_name($comment['staffid'])); ?>
            </a>
            <?php } else { ?>
            <?= '<b>' . _l('is_customer_indicator') . '</b>'; ?>
            <?php } ?>
            <small class="text-muted text-has-action" data-toggle="tooltip"
                data-title="<?= e(_dt($comment['dateadded'])); ?>">
                -
                <?= e(time_ago($comment['dateadded'])); ?></small>
        </div>
        <div data-proposal-comment="<?= e($comment['id']); ?>"
            class="tw-mt-3">
            <?= process_text_content_for_display($comment['content']); ?>
        </div>
        <div data-proposal-comment="<?= e($comment['id']); ?>"
            class="tw-mt-3">
            <div data-proposal-comment="<?= e($comment['id']); ?>" class="tw-mt-3">
            <?php 
            if (!empty($files)) { 
                foreach($files as $file){
                 ?>
                       <div class="comment-files">
                        <div class="file-row">
                            <a href="<?= base_url('uploads/proposal_comments/' . $file['file']); ?>" target="_blank">
                                <i class="fa fa-paperclip"></i> <?= $file['file'] ?>
                            </a>

                                <div class="final-file-option">
                                    <input type="checkbox" 
                                        class="final-file-checkbox" 
                                        data-file-id="<?= $file['id']; ?>" 
                                        data-proposal-id="<?= $file['proposal_id']; ?>"
                                        <?= $file['is_final'] == 1 ? 'checked' : '' ?>>
                                    <?= $file['is_final']==1 ? 'Final art work' : 'Mark as final art work'; ?>
                                </div>
                           
                        </div>
                    </div>
              <?php 
                }
             } 
            ?>
        </div>

        </div>
        <div data-proposal-comment-edit-textarea="<?= e($comment['id']); ?>"
            class="hide tw-mt-3">
            <?= render_textarea('comment-content', '', $comment['content']); ?>
            <?php if ($comment['staffid'] == get_staff_user_id() || is_admin()) { ?>
            <div class="text-right">
                <button type="button" class="btn btn-default"
                    onclick="toggle_proposal_comment_edit(<?= e($comment['id']); ?>);return false;">
                    <?= _l('cancel'); ?>
                </button>
                <button type="button" class="btn btn-primary"
                    onclick="edit_proposal_comment(<?= e($comment['id']); ?>);">
                    <?= _l('update_comment'); ?>
                </button>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php if ($i >= 0 && $i != $len - 1) {
        echo '<hr />';
    }
    ?>
</div>
<?php $i++;
} ?>
<script>
$(document).ready(function() {
    $('.final-file-checkbox').on('change', function() {
        var $checkbox = $(this); // keep reference
        var fileId = $checkbox.data('file-id');
        var proposalId = $checkbox.data('proposal-id');
        var isFinal = $checkbox.is(':checked') ? 1 : 0;
       
          $("body").append('<div class="dt-loader"></div>');
        var csrfName = $('input[name^="csrf"]').attr("name");
        var csrfHash = $('input[name^="csrf"]').val();
        
        var formData = new FormData();
        formData.append("file_id", fileId);
        formData.append("is_final", isFinal);
        formData.append(csrfName, csrfHash);
        $.ajax({
            url: admin_url + "proposals/set_final_file",
            type: "POST",
            data: formData,
            processData: false,   // required for files
            contentType: false,   // required for files
            dataType: "json",
            success: function(response) {
                $('body').find('.dt-loader').remove();
                
              
                if(response?.success) {
                    if (isFinal === 1) {
                        $('.final-file-checkbox[data-proposal-id="' + proposalId + '"]').not(this).prop('checked', false);
                    }
                     $checkbox.prop('checked', isFinal === 1);
                } else {
                    alert('Failed to update status.');
                    $checkbox.prop('checked', !isFinal);
                }
            },
            error: function() {
                $('body').find('.dt-loader').remove();
                alert('Error updating statusxx.');
            }
        });
    });
});
</script>
<style>
    .file-row {
    display: flex;
    justify-content: space-between; /* link on left, checkbox+text on right */
    align-items: center;
}

.final-file-option {
    display: flex;
    align-items: center;
    gap: 6px; /* spacing between checkbox and text */
}
    </style>