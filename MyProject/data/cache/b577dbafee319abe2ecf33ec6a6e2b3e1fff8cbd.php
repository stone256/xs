<?php $__env->startSection('content'); ?>
<div style="margin-left:10px;padding:6px 10px;border:5px #ddd solid">
<h3>"bar.blade.php"</h3>
<h4>Bar header</h4>

<ul>
	<li><?php echo 'You can use standard php line';?></li>
	<li>Or passed var $controller=<?php echo e($controller); ?></li>
	<li>aaaaaaaaaaaaaaaaaaaaaaaaaa</li>
</ul>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>