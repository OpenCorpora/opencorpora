SET NAMES utf8;

-- table `users` --> `users_for_selective_backup`

TRUNCATE TABLE `users_for_selective_backup`;

INSERT INTO `users_for_selective_backup` (
    `user_id`,
    `user_name`,
    `user_passwd`,
    `user_email`,
    `user_reg`,
    `user_shown_name`,
    `user_team`,
    `user_level`,
    `user_shown_level`,
    `user_rating10`,
    `show_game`
) SELECT
    `user_id`,
    `user_name`,
    '' AS `user_passwd`,
    '' AS `user_email`,
    `user_reg`,
    `user_shown_name`,
    `user_team`,
    `user_level`,
    `user_shown_level`,
    `user_rating10`,
    `show_game`
FROM `users`
WHERE 1 = 1;

-- table `user_tokens` --> `user_tokens_for_selective_backup`

TRUNCATE TABLE `user_tokens_for_selective_backup`;

INSERT INTO `user_tokens_for_selective_backup` (
    `user_id`,
    `token`,
    `timestamp`
) SELECT
    `user_id`,
    0 AS `token`,
    `timestamp`
FROM `user_tokens`
WHERE 1 = 1;
