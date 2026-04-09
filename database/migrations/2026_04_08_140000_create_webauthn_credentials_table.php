<?php

use Illuminate\Database\Schema\Blueprint;
use Laragear\WebAuthn\Models\WebAuthnCredential;

return WebAuthnCredential::migration()->with(function (Blueprint $table) {
    // Reserved for future passkey use.
});
