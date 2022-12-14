<?php

namespace Drupal\farm_ui_views\Access;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\farm_location\AssetLocationInterface;

/**
 * Checks access for displaying Views of assets in a location.
 */
class FarmLocationAssetViewsAccessCheck implements AccessInterface {

  /**
   * The asset storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $assetStorage;

  /**
   * The asset location service.
   *
   * @var \Drupal\farm_location\AssetLocationInterface
   */
  protected $assetLocation;

  /**
   * FarmLocationAssetViewsAccessCheck constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\farm_location\AssetLocationInterface $asset_location
   *   The asset location service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AssetLocationInterface $asset_location) {
    $this->assetStorage = $entity_type_manager->getStorage('asset');
    $this->assetLocation = $asset_location;
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function access(RouteMatchInterface $route_match) {

    // If there is no "asset" or "asset_type" parameter, bail.
    $asset_id = $route_match->getParameter('asset');
    $asset_type = $route_match->getParameter('asset_type');
    if (empty($asset_id) || empty($asset_type)) {
      return AccessResult::allowed();
    }

    // If the asset type is "all", bail.
    if ($asset_type == 'all') {
      return AccessResult::allowed();
    }

    // Allow access if the asset is a location.
    /** @var \Drupal\asset\Entity\AssetInterface $asset */
    $asset = $this->assetStorage->load($asset_id);
    $access = AccessResult::allowedIf($this->assetLocation->isLocation($asset));

    // Invalidate the access result when assets are changed.
    $access->addCacheTags(["asset_list"]);
    return $access;
  }

}
