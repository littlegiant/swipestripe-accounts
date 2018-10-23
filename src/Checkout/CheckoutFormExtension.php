<?php
declare(strict_types=1);

namespace SwipeStripe\Accounts\Checkout;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Security\Security;
use SwipeStripe\Accounts\Order\OrderExtension;
use SwipeStripe\Order\Checkout\CheckoutForm;
use SwipeStripe\Order\Order;

/**
 * Class CheckoutFormExtension
 * @package SwipeStripe\Accounts\Checkout
 * @property CheckoutForm|CheckoutFormExtension $owner
 */
class CheckoutFormExtension extends Extension
{
    /**
     * @config
     * @var bool
     */
    private static $enable_guest_checkout = true;

    const GUEST_OR_ACCOUNT_FIELD = 'GuestOrAccount';
    const ACCOUNT_PASSWORD_FIELD = 'AccountPassword';

    const CHECKOUT_GUEST = 'Guest';
    const CHECKOUT_CREATE_ACCOUNT = 'Account';

    /**
     *
     */
    public function beforeLoadDataFromCart(): void
    {
        /** @var Order|OrderExtension $cart */
        $cart = $this->owner->getCart();
        $cart->populateCustomerDefaults(Security::getCurrentUser());
    }

    /**
     * @param FieldList $fields
     */
    public function updateFields(FieldList $fields): void
    {
        if (Security::getCurrentUser()) {
            return;
        }

        if ($this->owner->guestCheckoutEnabled()) {
            $fields->add(OptionsetField::create(static::GUEST_OR_ACCOUNT_FIELD, '', [
                static::CHECKOUT_GUEST          => 'Checkout as guest',
                static::CHECKOUT_CREATE_ACCOUNT => 'Create an account',
            ], static::CHECKOUT_CREATE_ACCOUNT));
        }

        $fields->add(CheckoutPasswordField::create(static::ACCOUNT_PASSWORD_FIELD));
    }

    /**
     * @return bool
     */
    public function isGuestCheckout(): bool
    {
        $data = $this->owner->getData();
        return Security::getCurrentUser() === null &&
            $this->owner->guestCheckoutEnabled() &&
            $data[static::GUEST_OR_ACCOUNT_FIELD] === static::CHECKOUT_GUEST;
    }

    /**
     * @return bool
     */
    public function guestCheckoutEnabled(): bool
    {
        return CheckoutForm::config()->get('enable_guest_checkout');
    }
}
