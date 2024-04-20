<?php if ($assetExists("highlight.min.js")): ?>
    <script type="text/javascript" src="<?php echo $asset("highlight.min.js"); ?>"></script>
    <script type="text/javascript">
        hljs.initHighlightingOnLoad()
    </script>
<?php endif ?>
