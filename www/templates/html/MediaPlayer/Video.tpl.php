<?php

$height = 540;
$width  = 960;

$dimensions = $context->getVideoDimensions();
if (isset($dimensions[0])) {
    // Scale everything down to 450 wide
    $height = round(($width/$dimensions[0])*$dimensions[1]);
}

$autoplay = 'autoplay';
if ($parent->context instanceof UNL_MediaHub_Media_Preview) {
    $autoplay = '';
}

//Don't auto play on the addmedia view
if (isset($controller->options['view']) && $controller->options['view'] == 'addmedia') {
    $autoplay = '';
}

if (isset($controller->options['autoplay']) && !$controller->options['autoplay']) {
    $autoplay = '';
}

?>
<video class="wdn_player" style="width:100%;height:100%" <?php echo $autoplay; ?> src="<?php echo $context->url; ?>" controls data-mediahub-id="<?php echo $context->id ?>" data-url="<?php echo $controller->getURL($context); ?>" poster="<?php echo $context->getThumbnailURL(); ?>" title="<?php echo $context->title; ?>" crossorigin="anonymous">
	<track src="<?php echo $context->getVideoTextTrackURL(); ?>" kind="subtitles" srclang="en" />
</video>
