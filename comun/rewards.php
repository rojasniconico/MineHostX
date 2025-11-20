<?php
// comun/rewards.php
if (!function_exists('giveTemporaryReward')) {
    function giveTemporaryReward($conn, $user_id, $type, $value, $duration_hours) {
        $user_id = intval($user_id);
        if ($user_id <= 0) return false;
        $type = mysqli_real_escape_string($conn, $type);
        $value = mysqli_real_escape_string($conn, $value);
        $expires = date("Y-m-d H:i:s", time() + max(0,intval($duration_hours))*3600);
        mysqli_query($conn, "INSERT INTO temporary_rewards (user_id, reward_type, value, expires_at) VALUES ($user_id,'$type','$value','$expires')");
        return true;
    }
}

if (!function_exists('clearExpiredRewards')) {
    function clearExpiredRewards($conn) {
        mysqli_query($conn, "DELETE FROM temporary_rewards WHERE expires_at < NOW()");
    }
}
