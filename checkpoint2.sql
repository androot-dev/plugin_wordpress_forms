"
START TRANSACTION;

INSERT INTO `wp_iu_forms` (`id`, `name`, `description`, `categories`, `url`, `notes`, `created_at`, `updated_at`) VALUES
(4044, 'AR-11 | Alien’s Change of Address Card', 'File OnlineAll noncitizens in the United States must report a change of address to USCIS within 10 days (except A and G visa holders and visa waiver visitors) of moving. This helps ensure that we mail important notices and documents to the right place. ', '[2671]', 'https://www.uscis.gov/ar-11', '', '2023-01-19 14:18:45', '2023-01-19 14:18:45');


INSERT INTO `wp_iu_online_files` (`id`, `name`, `url`, `created_at`, `updated_at`) VALUES
(949, 'AR-11, Alien’s Change of Address Card (PDF, 284.85 KB)', 'https://www.uscis.gov/sites/default/files/document/forms/ar-11.pdf', '2023-01-19 14:19:45', '2023-01-19 14:19:45');

INSERT INTO `wp_iu_online_files_link_forms` (`id`, `id_form`, `id_file`, `created_at`, `updated_at`) VALUES
(783, 4044, 949, '2023-01-19 14:21:04', '2023-01-19 14:21:04');


INSERT INTO `wp_iu_questions` (`id`, `question`, `type`, `placeholder`, `options`, `info`, `class`, `name_pdf_field`, `created_at`, `updated_at`) VALUES
(1, 'First name', 'text', 'First Name', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'first-name', '2023-01-26 14:12:26', '2023-01-27 02:04:51'),
(2, 'Middle Name (if any)', 'text', 'Middle Name', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'middle-name', '2023-01-26 14:12:58', '2023-01-26 15:29:02'),
(3, 'Last Name', 'text', 'Last Name', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'last-name', '2023-01-26 14:13:13', '2023-01-26 15:29:12'),
(4, 'Date of birth', 'date', '0/00/0000', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'date-of-birth', '2023-01-26 14:56:33', '2023-01-26 15:29:21'),
(6, 'A-number', 'text', 'A-number (if-any)', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', 'The A-number is a nine-digit number beginning with the letter “A” and would appear at the top of any notices previously received from USCIS. It can also be found on an Employment Authorization Document (work permit card), Refugee Travel Document, or Permanent Resident Card, among other documents, issued by USCIS.', 'separator-after', 'serial', '2023-01-26 15:27:35', '2023-01-27 22:21:18'),
(15, 'Physical Address', 'text', 'Number and Street', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', 'field-expanded', 'present-street-number-and-name', '2023-01-27 02:07:51', '2023-01-27 22:22:21'),
(21, '', 'radio', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[\"Apt\",\"Ste\",\"Flr\"]}', NULL, NULL, 'present-apt-check', '2023-01-27 02:25:50', '2023-01-27 20:20:51'),
(22, 'Apt Number', 'text', 'Apt Number', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'present-number', '2023-01-27 02:27:08', '2023-01-27 04:12:53'),
(23, 'City or Town', 'text', 'City or Town', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'present-city-or-town', '2023-01-27 02:27:43', '2023-01-27 04:12:58'),
(24, 'State', 'text', 'State', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'present-state', '2023-01-27 02:28:26', '2023-01-27 04:13:03'),
(25, 'Country', 'text', 'Country', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:28:43', '2023-01-27 04:13:07'),
(26, 'Zip code', 'text', 'Zip code', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'present-zip-code', '2023-01-27 02:28:59', '2023-01-27 23:16:00'),
(27, 'Province', 'text', 'Province', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:29:08', '2023-01-27 04:13:21'),
(28, 'Postal Code', 'text', 'Postal Code', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:29:26', '2023-01-27 04:13:30'),
(29, 'Phone Number', 'text', 'Phone Number', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:32:17', '2023-01-27 04:13:36'),
(30, 'From', 'date', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:32:32', '2023-01-27 02:32:32'),
(34, 'Does {{name}} have the same physical and mailing address?', 'radio', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"hooks\":{\"is_equals\":\"Yes\",\"on\":\"localaddress_to_mailingaddress\",\"off\":\"off_localaddress_to_mailingaddress\"},\"options\":[\"Yes\",\"No\"]}', '', 'field-expanded separator-before', '', '2023-01-27 22:48:47', '2023-01-28 00:31:38'),
(44, 'Postal Code', 'text', 'Postal Code', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:29:26', '2023-01-27 04:13:30'),
(45, 'Phone Number', 'text', 'Phone Number', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, '', '2023-01-27 02:32:17', '2023-01-27 04:13:36'),
(46, '{{name}}\'s Mailing Address', 'text', 'Number and Street', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[\"Yes \",\"No\"]}', '', 'field-expanded', 'mailing-street-number-and-name', '2023-01-27 22:59:20', '2023-01-27 23:47:49'),
(47, '', 'radio', '', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[\"Apt\",\"Ste\",\"Flr\"]}', '', '', 'mailing-apt-check', '2023-01-27 23:00:36', '2023-01-28 00:31:06'),
(48, 'Apt Number', 'text', 'Apt Number', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', 'mailing-number', '2023-01-27 23:07:38', '2023-01-27 23:49:02'),
(49, 'City or Town', 'text', 'City or Town', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', 'mailing-city-or-town', '2023-01-27 23:08:03', '2023-01-27 23:48:00'),
(50, 'State', 'text', 'State', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', 'mailing-state', '2023-01-27 23:08:11', '2023-01-27 23:48:09'),
(51, 'Country', 'text', 'Country', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', NULL, '2023-01-27 23:08:30', '2023-01-27 23:11:46'),
(52, 'Zip Code', 'text', 'Zip Code', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', 'mailing-zip-code', '2023-01-27 23:08:37', '2023-01-27 23:48:43'),
(53, 'Province', 'text', 'Province', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', NULL, '2023-01-27 23:08:47', '2023-01-27 23:16:24'),
(54, 'Postal Code', 'text', 'Postal Code', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', NULL, '2023-01-27 23:08:56', '2023-01-27 23:16:30'),
(55, 'Phone Number', 'text', 'Phone Number', '{\"methods\":{\"only_show\":\"34\",\"value_is\":[\"No\"]},\"options\":[]}', '', '', NULL, '2023-01-27 23:09:38', '2023-01-27 23:16:37'),
(57, 'Please write your old address', 'text', 'Number and Street', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', 'separator-before field-expanded', 'previous-street-number-and-name', '2023-01-27 23:27:31', '2023-01-27 23:45:55'),
(58, '', 'radio', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[\"Apt\",\"Ste\",\"Flr\"]}', '', '', 'previous-apt-check', '2023-01-27 23:36:03', '2023-01-28 00:33:43'),
(59, 'Apt Number', 'text', 'Apt Number', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', 'previous-number', '2023-01-27 23:36:36', '2023-01-27 23:46:59'),
(60, 'City or Town', 'text', 'City or Town', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', 'previous-city-or-town', '2023-01-27 23:36:52', '2023-01-27 23:46:10'),
(61, 'State', 'text', 'State', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', NULL, 'previous-state', '2023-01-27 23:37:09', '2023-01-27 23:46:28'),
(62, 'Country', 'text', 'Country', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:38:35', '2023-01-27 23:38:35'),
(63, 'Zip Code', 'text', 'Zip Code', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', 'previous-zip-code', '2023-01-27 23:38:47', '2023-01-27 23:46:44'),
(64, 'Province', 'text', 'Province', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:38:59', '2023-01-27 23:38:59'),
(65, 'Postal Code', 'text', 'Postal Code', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:39:31', '2023-01-27 23:39:31'),
(66, 'Phone Number', 'text', 'Phone Number', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:39:53', '2023-01-27 23:39:53'),
(67, 'From', 'date', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:40:21', '2023-01-27 23:40:21'),
(68, 'To', 'date', '', '{\"methods\":{\"only_show\":\"\",\"value_is\":\"\"},\"options\":[]}', '', '', NULL, '2023-01-27 23:40:34', '2023-01-27 23:40:34');

INSERT INTO `wp_iu_questions_groups` (`id`, `name`, `questions_ids`, `created_at`, `updated_at`) VALUES
(13, 'General', '1,2,3,4,6,12,13,15,19,20,21,22,23,24,25,26,27,28,29,30,34,35,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68', '2023-01-26 14:12:26', '2023-01-27 23:40:34');

INSERT INTO `wp_iu_questions_groups_link_online_files` (`id`, `id_file`, `groups_ids`, `created_at`, `updated_at`) VALUES
(15, 949, '13', '2023-01-27 00:25:40', '2023-01-27 03:31:28');


COMMIT;
"