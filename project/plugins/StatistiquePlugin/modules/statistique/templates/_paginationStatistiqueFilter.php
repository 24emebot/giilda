<nav>
  <ul class="pager">
    <li>
    	<?php if ($page > 1): ?>
			<a href="<?php echo url_for('statistiques_'.$type, array('p' => ($page - 1))) ?>"><span aria-hidden="true">&larr;</span>&nbsp;Précédent</a>
		<?php endif; ?>
    </li>
    <li>(<strong><?php echo $page ?></strong>/<?php echo $nbPage ?>)</li>
    <li>
    	<?php if ($page < $nbPage): ?>
			<a href="<?php echo url_for('statistiques_'.$type, array('p' => ($page + 1))) ?>">Suivant&nbsp;<span aria-hidden="true">&rarr;</span></a>
		<?php endif; ?>
    </li>
  </ul>
</nav>