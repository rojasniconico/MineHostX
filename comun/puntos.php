<?php
function addPoints($conn, $user_id, $amount, $reason = '') {
    $user_id = intval($user_id);
    $amount = intval($amount);
    $reason = mysqli_real_escape_string($conn, $reason);

    if ($user_id <= 0) return false;

    mysqli_query($conn, "UPDATE users SET points = COALESCE(points,0) + $amount WHERE id = $user_id");
    mysqli_query($conn, "INSERT INTO user_points_history (user_id, amount, reason) VALUES ($user_id, $amount, '$reason')");
    return true;
}

function removePoints($conn, $user_id, $amount, $reason = '') {
    $user_id = intval($user_id);
    $amount = intval($amount);
    $reason = mysqli_real_escape_string($conn, $reason);

    if ($user_id <= 0) return false;

    mysqli_query($conn, "UPDATE users SET points = GREATEST(COALESCE(points,0) - $amount, 0) WHERE id = $user_id");
    mysqli_query($conn, "INSERT INTO user_points_history (user_id, amount, reason) VALUES ($user_id, -$amount, '$reason')");
    return true;
}
