<?php

namespace App\DTOs;

use App\Models\User;

class CheckoutData
{
    public User     $user;
    public ?int     $addressId;
    public ?string  $country;
    public ?string  $addressLine1;
    public ?string  $addressLine2;
    public ?string  $city;
    public ?string  $state;
    public ?string  $postalCode;
    public ?string  $phone;
    public string   $paymentMethod;
    public ?string  $cardNumber;
    public ?string  $cvc;
    public ?string  $expireDate;

    private function __construct(User $user, array $data)
    {
        $this->user           = $user;
        $this->addressId      = $data['address_id']     ?? null;
        $this->country        = $data['country']        ?? null;
        $this->addressLine1   = $data['address_line1']  ?? null;
        $this->addressLine2   = $data['address_line2']  ?? null;
        $this->city           = $data['city']           ?? null;
        $this->state          = $data['state']          ?? null;
        $this->postalCode     = $data['postal_code']    ?? null;
        $this->phone          = $data['phone']          ?? null;
        $this->paymentMethod  = $data['payment_method'];
        $this->cardNumber     = $data['card_number']    ?? null;
        $this->cvc            = $data['cvc']            ?? null;
        $this->expireDate     = $data['expire_date']    ?? null;
    }

    public static function create(User $user, array $validated): self
    {
        return new self($user, $validated);
    }
}
