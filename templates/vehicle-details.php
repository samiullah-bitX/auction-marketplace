<h2><?= esc_html($car['year'] ?? '') ?> <?= esc_html($car['make'] ?? '') ?> <?= esc_html($car['model'] ?? '') ?></h2>
<p>VIN: <?= esc_html($car['vin']) ?></p>
<p>Location: <?= esc_html($car['location']) ?></p>
<p>Damage: <?= esc_html($car['primary_damage'] ?? '') ?></p>

<?php if (!empty($car['car_photo']['photo'])): ?>
    <div class="photo-gallery">
        <?php foreach ($car['car_photo']['photo'] as $url): ?>
            <img src="<?= esc_url($url) ?>" style="max-width:200px;" />
        <?php endforeach; ?>
    </div>
<?php endif; ?>
