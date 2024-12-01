CREATE TABLE `model_chats` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(20) NOT NULL,
  `number` varchar(20) NOT NULL,
  `role` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `model_chats`
  ADD PRIMARY KEY (`id`);
