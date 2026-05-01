<?php
declare(strict_types=1);

namespace DalmoaCore\Admin\Dashboard;

final class AdDashboardPage
{
    public function render(): void
    {
        $stats = $this->getStats();
        ?>
        <div class="wrap">
            <h1>광고 대시보드</h1>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:20px;">
                <?php $this->card('총 매출', '$' . number_format($stats['revenue'])); ?>
                <?php $this->card('월 반복 매출(MRR)', '$' . number_format($stats['mrr'])); ?>
                <?php $this->card('활성 광고', $stats['activeAds']); ?>
                <?php $this->card('활성 구독', $stats['activeSubscriptions']); ?>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:20px;">
                <?php $this->card('노출 수', number_format($stats['impressions'])); ?>
                <?php $this->card('클릭 수', number_format($stats['clicks'])); ?>
                <?php $this->card('CTR', $stats['ctr'] . '%'); ?>
                <?php $this->card('취소/위험 구독', $stats['cancelledSubscriptions']); ?>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:20px;">
                <?php $this->card('Premium 광고', $stats['premium']); ?>
                <?php $this->card('Featured 광고', $stats['featured']); ?>
                <?php $this->card('평균 CPC', '$' . number_format($stats['averageCpc'], 2)); ?>
                <?php $this->card('평균 CPM', '$' . number_format($stats['averageCpm'], 2)); ?>
            </div>

            <h2 style="margin-top:40px;">최근 광고</h2>

            <table class="widefat fixed striped" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>제목</th>
                        <th>상품</th>
                        <th>결제방식</th>
                        <th>구독상태</th>
                        <th>입찰가</th>
                        <th>노출</th>
                        <th>클릭</th>
                        <th>CTR</th>
                        <th>만료일</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['recent'])): ?>
                        <tr>
                            <td colspan="9">표시할 광고가 없습니다.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($stats['recent'] as $row): ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url($row['editUrl']); ?>">
                                    <?php echo esc_html($row['title']); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($row['plan']); ?></td>
                            <td><?php echo esc_html($row['billingType']); ?></td>
                            <td><?php echo esc_html($row['subscriptionStatus']); ?></td>
                            <td>$<?php echo esc_html(number_format((float) $row['bid'], 2)); ?></td>
                            <td><?php echo esc_html(number_format((int) $row['impressions'])); ?></td>
                            <td><?php echo esc_html(number_format((int) $row['clicks'])); ?></td>
                            <td><?php echo esc_html((string) $row['ctr']); ?>%</td>
                            <td><?php echo esc_html($row['endsAt']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function card(string $title, string|int|float $value): void
    {
        ?>
        <div style="background:#fff;border:1px solid #e5e7eb;padding:16px;border-radius:8px;">
            <div style="font-size:12px;color:#6b7280;"><?php echo esc_html($title); ?></div>
            <div style="font-size:20px;font-weight:bold;margin-top:4px;"><?php echo esc_html((string) $value); ?></div>
        </div>
        <?php
    }

    private function getStats(): array
    {
        $posts = get_posts([
            'post_type' => 'ad_listing',
            'post_status' => ['publish', 'pending', 'draft'],
            'posts_per_page' => -1,
        ]);

        $revenue = 0.0;
        $mrr = 0.0;
        $activeAds = 0;
        $impressions = 0;
        $clicks = 0;
        $premium = 0;
        $featured = 0;
        $activeSubscriptions = 0;
        $cancelledSubscriptions = 0;
        $recent = [];

        foreach ($posts as $post) {
            $id = (int) $post->ID;

            $plan = (string) get_post_meta($id, 'ad_plan', true);
            $billingType = (string) get_post_meta($id, 'billing_type', true);
            $subscriptionStatus = (string) get_post_meta($id, 'subscription_status', true);
            $isActive = get_post_meta($id, 'is_active', true) !== '0';
            $impression = (int) get_post_meta($id, 'impression_count', true);
            $click = (int) get_post_meta($id, 'click_count', true);
            $endsAt = (string) get_post_meta($id, 'ad_ends_at', true);
            $bid = (float) get_post_meta($id, 'bid_amount', true);

            $billingType = $billingType !== '' ? $billingType : 'one_time';
            $subscriptionStatus = $subscriptionStatus !== '' ? $subscriptionStatus : '-';

            if ($isActive) {
                $activeAds++;
            }

            $impressions += $impression;
            $clicks += $click;

            if (in_array($plan, ['premium', 'premium_monthly'], true)) {
                $premium++;
            }

            if (in_array($plan, ['featured', 'featured_monthly'], true)) {
                $featured++;
            }

            if ($plan === 'premium') {
                $revenue += 99;
            } elseif ($plan === 'featured') {
                $revenue += 49;
            } elseif ($plan === 'premium_monthly') {
                $mrr += 99;
            } elseif ($plan === 'featured_monthly') {
                $mrr += 49;
            }

            if ($billingType === 'subscription') {
                if (in_array($subscriptionStatus, ['active', 'trialing'], true)) {
                    $activeSubscriptions++;
                }

                if (in_array($subscriptionStatus, ['cancelled', 'canceled', 'unpaid', 'past_due'], true)) {
                    $cancelledSubscriptions++;
                }
            }

            $ctr = $impression > 0 ? round(($click / $impression) * 100, 1) : 0;

            $recent[] = [
                'title' => $post->post_title,
                'editUrl' => get_edit_post_link($id, ''),
                'plan' => strtoupper($plan ?: 'basic'),
                'billingType' => strtoupper($billingType),
                'subscriptionStatus' => strtoupper($subscriptionStatus),
                'bid' => $bid,
                'impressions' => $impression,
                'clicks' => $click,
                'ctr' => $ctr,
                'endsAt' => $endsAt ?: '-',
            ];
        }

        usort($recent, function (array $a, array $b): int {
            $aTime = $a['endsAt'] !== '-' ? strtotime($a['endsAt']) : 0;
            $bTime = $b['endsAt'] !== '-' ? strtotime($b['endsAt']) : 0;

            return $bTime <=> $aTime;
        });

        $recent = array_slice($recent, 0, 10);

        $ctrTotal = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        $averageCpc = $clicks > 0 ? $revenue / $clicks : 0;
        $averageCpm = $impressions > 0 ? ($revenue / $impressions) * 1000 : 0;

        return [
            'revenue' => $revenue,
            'mrr' => $mrr,
            'activeAds' => $activeAds,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $ctrTotal,
            'premium' => $premium,
            'featured' => $featured,
            'activeSubscriptions' => $activeSubscriptions,
            'cancelledSubscriptions' => $cancelledSubscriptions,
            'averageCpc' => $averageCpc,
            'averageCpm' => $averageCpm,
            'recent' => $recent,
        ];
    }
}