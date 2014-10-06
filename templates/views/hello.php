<?php // templates/views/hello.php ?>
<?php $this->title()->set("Hello from aura"); ?>
<p>Hello <?= $this->escape()->html($this->name); ?></p>
