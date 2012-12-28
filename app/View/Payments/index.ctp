<h1><?php echo __("Demo"); ?></h1>

<p><?php echo $this->Html->link('PayPal Express' , array('action' => 'express_checkout')); ?></p>

<p><?php echo $this->Html->link('Direct Payment' , array('action' => 'charge_card')); ?></p>