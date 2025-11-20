<?php

function generateReferralCode($length = 8) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, $length);
}

function getOrCreateReferralCode($conn, $user_id) {

    // 1. ¿Ya existe?
    $result = mysqli_query($conn, "
        SELECT code FROM referral_codes WHERE user_id = $user_id LIMIT 1
    ");

    if ($row = mysqli_fetch_assoc($result)) {
        return $row["code"];
    }

    // 2. Crear uno nuevo
    $new_code = generateReferralCode();

    mysqli_query($conn, "
        INSERT INTO referral_codes (user_id, code)
        VALUES ($user_id, '$new_code')
    ");

    return $new_code;
}

