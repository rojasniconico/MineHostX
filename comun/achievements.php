<?php
// comun/achievements.php
if (!function_exists('giveAchievement')) {
    function giveAchievement($conn, $user_id, $code) {
        $user_id = intval($user_id);
        if ($user_id <= 0) return false;

        $code_safe = mysqli_real_escape_string($conn, $code);
        $ach = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT * FROM achievements WHERE code='$code_safe' LIMIT 1"
        ));
        if (!$ach) return false;
        $aid = intval($ach['id']);

        // comprobar si ya lo tiene
        $exists = mysqli_query($conn, "SELECT id FROM user_achievements WHERE user_id=$user_id AND achievement_id=$aid");
        if (mysqli_num_rows($exists) > 0) return false;

        mysqli_query($conn, "INSERT INTO user_achievements (user_id, achievement_id) VALUES ($user_id, $aid)");

        // dar puntos si aplica
        if (!empty($ach['points_reward']) && intval($ach['points_reward']) > 0) {
            require_once __DIR__ . "/puntos.php";
            addPoints($conn, $user_id, intval($ach['points_reward']), "Logro desbloqueado: ".$ach['title']);
        }

        // Guardar en session para popup inmediato
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['achievement_unlocked'] = [
            'title' => $ach['title'],
            'desc'  => $ach['description'],
            'icon'  => $ach['badge_icon'] ?? '🏆'
        ];

        return true;
    }
}
