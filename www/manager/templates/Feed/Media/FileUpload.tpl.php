<?php
// generate a random upload identifier
$upload_id = md5(microtime() . rand());

function return_bytes($val)
{
    //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($val, -1);
    $ret = substr($val, 0, -1);
    switch(strtoupper($l)){
        case 'P':
            $ret *= 1024;
        case 'T':
            $ret *= 1024;
        case 'G':
            $ret *= 1024;
        case 'M':
            $ret *= 1024;
        case 'K':
            $ret *= 1024;
            break;
    }
    return $ret;
}

?>
<script type="text/javascript">
var upload = function() {
    
    /* private variables */
    
    var uploadprogress = null;
    
    var startTime = null;
    var upload_max_filesize = <?php echo return_bytes(ini_get('upload_max_filesize'));?>;
    
    var infoUpdated = 0;
    
    var writeStatus = function(text,color) {
        var statDiv = document.getElementById("uploadstatus");
        if (color == 1 ) {
            statDiv.style.backgroundColor = "green";
        } else if (color == 2 ) {
            statDiv.style.backgroundColor = "orange";
        } else if (color == 3 ) {
            statDiv.style.backgroundColor = "red";
        } else {
            statDiv.style.backgroundColor = "white";
        }
        statDiv.innerHTML = text;
    }

    return {
        start: function() {
           uploadprogress = document.getElementById("uploadprogress");
           startTime = new Date();
           infoUpdated = 0;
           
           // Show progress bar
           WDN.jQuery('#progress').show();
           
           // Hide the submit button until the upload is complete.
           WDN.jQuery('#continue3').attr('disabled', 'disabled');
           
           WDN.jQuery('.uploading').show();
           
           this.requestInfo();
        },
        stop: function(url) {
           if (typeof url == 'undefined' || url) {
                var secs = (new Date() - startTime)/1000;
                var statusText = "Upload succeeded, it took " + secs + " seconds. <br/> ";
                if (infoUpdated > 0) {
                    // Upload succeeded and we had intermediate status updates
                    writeStatus(statusText + "You had " + infoUpdated + " updates from the progress meter, looks like it's working fine",1);
                } else {
                    statusText += "BUT there were no progress meter updates<br/> ";
                    if (secs < 3) {
                      writeStatus(statusText + "Your upload was maybe too short, try with a bigger file or a slower connection",2);
                    } else {
                      writeStatus(statusText + "Your upload should have taken long enough to have an progress update. Maybe it really does not work...",3);
                    }
                }

                // Hide upload progress
                WDN.jQuery('#progress').hide();
                
                WDN.jQuery('.uploading').hide();
                
                //Show the continue button.
                WDN.jQuery('#continue3').removeAttr('disabled');

                // Set the URL within the form
                WDN.jQuery("#media_url").attr("value", url);

                // Grab the preview & player
                mediaDetails.getPreview(url);

           } else {
               writeStatus('PHP did not report any uploaded file, maybe it was too large, try a smaller one (post_max_size: <?php echo ini_get('post_max_size');?>)',3);
           }
           startTime = null;
        },
        requestInfo: function() {
            uploadprogress.src="?view=uploadprogress&format=barebones&id=<?php echo $upload_id; ?>&"+new Date();
        },

        updateInfo: function(uploaded, total, startTime, percentage) {
            //We made it this far, that means the progress bar should be working in older browsers.
            WDN.jQuery('.meter').show();
            
            if (uploaded) {
                infoUpdated++;
                if (total > upload_max_filesize) {
                    writeStatus("The file is too large and won't be available for PHP after the upload<br/> Your file size is " + total + " bytes. Allowed is " + upload_max_filesize + " bytes.", 2);
                } else {

                    WDN.jQuery(".meter > span").animate({
                      width: percentage+'%'
                    }, 1000);

                }
            } else {
                writeStatus("Upload started since " + (new Date() - startTime)/1000 + " seconds. No progress info yet");
            }
            window.setTimeout("upload.requestInfo()", 1000);
            //WDN.jQuery('#media_form').html(uploaded);
        }
    }
}()
</script>
<form id="fileUpload" onsubmit="upload.start()" target="uploadtarget" action="?format=barebones" enctype="multipart/form-data" method="post" class="zenform cool">
    <input type="hidden" name="APC_UPLOAD_PROGRESS" value="<?php echo $upload_id;?>" />
    <input type="hidden" name="__unlmy_posttarget" value="upload_media" />
    <fieldset id="addMedia">
    <legend>Add New Media</legend>
        <ol>
            <li>
                <label><span class="required">*</span>URL of Media File <span class="helper">Media types supported: .m4v, .mp4, .mp3</span></label>
                <input id="url" name="url" type="text" value="<?php echo htmlentities(@$context->media->url, ENT_QUOTES); ?>" />
                <?php
                $max_upload_size = min(return_bytes(ini_get('post_max_size')), return_bytes(ini_get('upload_max_filesize')));
                ?>
                <label>Or, Upload Media <span class="helper"><?php echo "Maximum upload file size is ".($max_upload_size/(1024*1024))."MB." ?></span></label>
                <input id="file_upload" name="file_upload" type="file" />
            </li>
        </ol>
        <input type="submit" name="submit" id="mediaSubmit" value="Add Media" />
    </fieldset>
</form>
<div id='progress' class='grid11' style="display:none;">
    <h2>Your media is being uploaded <img class='uploading' src="/wdn/templates_3.0/scripts/plugins/tinymce/themes/advanced/skins/unl/img/progress.gif" alt="progress animated gif" /></h2>
    <div class="meter animate">
        <span style="width:0%;"><span></span></span>
    </div>
    <span>While you wait, please fill out the form below.</span>
</div>
<div id="uploadstatus" style="display:none;"></div>
<iframe name="uploadprogress" width="1" height="1" id="uploadprogress"></iframe>
<iframe name="uploadtarget" width="1" height="1" id="uploadtarget"></iframe>