<?php if (!empty($members)): ?>
<div class="members-list">
	<?php foreach ($members as $member): ?>
	<div class="member-item" style="display: flex; align-items: center; padding: 12px; border-bottom: 1px solid #f1f5f9;">
		<div class="member-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
			<?php if (!empty($member->photo)): ?>
				<img src="<?= get_image_url('staff', $member->photo) ?>" alt="<?= $member->name ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
			<?php else: ?>
				<i class="fa fa-user" style="color: #0369a1;"></i>
			<?php endif; ?>
		</div>
		<div class="member-info">
			<div class="member-name" style="font-weight: 600; color: #1e293b;"><?= $member->name ?></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align: center; padding: 40px; color: #6b7280;">
	<i class="fa fa-users" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
	<p>No members in this department yet.</p>
</div>
<?php endif; ?>