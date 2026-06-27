<?php
/**
 * Support / donate tab.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_number = '6219861894137982';
$card_pretty = '6219-8618-9413-7982';
?>
<div class="amazing-offer-support">
	<div class="amazing-offer-support-card">

		<div class="ao-support-avatar">
			<span class="dashicons dashicons-businessman"></span>
		</div>

		<h2 class="ao-support-name">Danial Bakhtiari</h2>
		<p class="ao-support-role"><?php esc_html_e( 'توسعه‌دهنده وردپرس', 'amazing-offer' ); ?></p>
		<a class="ao-support-site" href="https://danialbakhtiari.com" target="_blank" rel="noopener noreferrer">danialbakhtiari.com</a>

		<blockquote class="ao-support-quote">
			<?php esc_html_e( 'اگر این پلاگین برات مفید بود، با یک فنجان قهوه حمایتم کن! ☕', 'amazing-offer' ); ?>
		</blockquote>

		<div class="ao-support-pay">
			<div class="ao-pay-head">
				<span class="dashicons dashicons-money-alt"></span>
				<?php esc_html_e( 'پرداخت مستقیم کارت به کارت', 'amazing-offer' ); ?>
			</div>

			<div class="ao-pay-bank">
				<strong><?php esc_html_e( 'بانک بلو', 'amazing-offer' ); ?></strong>
				<span><?php esc_html_e( 'به نام: دانیال بختیاری‌نژاد', 'amazing-offer' ); ?></span>
			</div>

			<div class="ao-pay-card">
				<code id="ao-card-number" data-card="<?php echo esc_attr( $card_number ); ?>"><?php echo esc_html( $card_pretty ); ?></code>
				<button type="button" class="button ao-copy-card" data-card="<?php echo esc_attr( $card_number ); ?>">
					<span class="dashicons dashicons-admin-page"></span>
					<?php esc_html_e( 'کپی', 'amazing-offer' ); ?>
				</button>
			</div>
			<span class="ao-copy-feedback" aria-live="polite"></span>
		</div>

		<div class="ao-support-links">
			<a class="button button-secondary" href="https://danialbakhtiari.com" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-admin-site-alt3"></span> <?php esc_html_e( 'وب‌سایت', 'amazing-offer' ); ?>
			</a>
			<a class="button button-secondary" href="https://wordpress.org/plugins/" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'امتیاز در مخزن', 'amazing-offer' ); ?>
			</a>
		</div>
	</div>
</div>
