<?php if ($assetExists("highlight.min.css")): ?>
    <link rel="stylesheet" href="<?php echo $asset("highlight.min.css"); ?>" type="text/css" />
<?php endif ?><?php if ($assetExists("base.css")): ?>
    <link rel="stylesheet" href="<?php echo $asset("base.css"); ?>" type="text/css" />
<?php endif ?><?php if ($assetExists("style.css")): ?>
    <link rel="stylesheet" href="<?php echo $asset("style.css"); ?>" type="text/css" />
<?php endif ?><?php if ($assetExists("background.jpg")): ?>
    <style>
        html {background-image: url("<?php echo $asset("background.jpg")?>")}
    </style>
<?php endif ?>

