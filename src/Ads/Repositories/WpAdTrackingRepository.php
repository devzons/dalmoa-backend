public function increment(int $postId, string $type, string $placement): array
{
    $totalKey = $type === 'click' ? 'click_count' : 'impression_count';
    $placementKey = "{$totalKey}_" . sanitize_key($placement);

    $total = (int) get_post_meta($postId, $totalKey, true);
    $placementTotal = (int) get_post_meta($postId, $placementKey, true);

    update_post_meta($postId, $totalKey, $total + 1);
    update_post_meta($postId, $placementKey, $placementTotal + 1);

    return [
        'total' => $total + 1,
        'placement' => $placementTotal + 1,
    ];
}