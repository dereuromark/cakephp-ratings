<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * Used by RatableBehaviorTest to verify the rating-cache writes survive a
 * locked-down `$patchable` map. Without the behavior force-bypassing
 * `patchableFields`, the patch would silently drop `rating`, `rating_sum`,
 * and `rating_count` and the cache would drift from the underlying ratings.
 */
class LockedPost extends Entity {

	protected array $patchable = [
		'*' => false,
	];

}
