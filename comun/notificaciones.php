<?php
function addNotification($conn, $user_id, $message, $type = 'info') {
    if (!$user_id || !$conn) return;
    $msg = mysqli_real_escape_string($conn, $message);
    $type = mysqli_real_escape_string($conn, $type);
    mysqli_query($conn, "
        INSERT INTO user_notifications (user_id, message, type)
        VALUES ($user_id, '$msg', '$type')
    ");
}

function getUnreadNotifications($conn, $user_id) {
    $res = mysqli_query($conn, "
        SELECT id, message, type, created_at
        FROM user_notifications
        WHERE user_id=$user_id AND seen=0
        ORDER BY created_at DESC
        LIMIT 10
    ");
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function markNotificationsAsRead($conn, $user_id) {
    mysqli_query($conn, "UPDATE user_notifications SET seen=1 WHERE user_id=$user_id");
}


function notifyAllUsers($conn, $message, $type = 'event') {
    $msg = mysqli_real_escape_string($conn, $message);
    $type = mysqli_real_escape_string($conn, $type);
    mysqli_query($conn, "
        INSERT INTO user_notifications (user_id, message, type)
        SELECT id, '$msg', '$type' FROM users
    ");
}

?>
