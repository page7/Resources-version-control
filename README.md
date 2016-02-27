Resources Version Control
-------
This is a simple version controlor for resources.

USE
======
Setting:
tool\version::$paths --- specify resource's path by file extension
tool\version::$cache --- save version data options

Do:
<link href="<?php tool\version::load('bootstrap.min.css'); ?>" rel="stylesheet" />
<script src="<?php tool\version::load('jquery.js'); ?>"></script>
<link href="<?php tool\version::load('respond-proxy.html', '/res/js/'); ?>" id="respond-proxy" rel="respond-proxy" />