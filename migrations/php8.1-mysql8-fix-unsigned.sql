SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE comment MODIFY id INT UNSIGNED;
ALTER TABLE comment MODIFY post_id INT UNSIGNED;
ALTER TABLE comment MODIFY user_id INT UNSIGNED;
ALTER TABLE config MODIFY id INT UNSIGNED;
ALTER TABLE dirtyword MODIFY id INT UNSIGNED;
ALTER TABLE dirtyword MODIFY language_id INT UNSIGNED;
ALTER TABLE emoticon MODIFY id INT UNSIGNED;
ALTER TABLE event MODIFY id INT UNSIGNED;
ALTER TABLE groupcache MODIFY group_id INT UNSIGNED;
ALTER TABLE groupcache MODIFY id INT UNSIGNED;
ALTER TABLE groupcache MODIFY instance_id INT UNSIGNED;
ALTER TABLE image MODIFY group_id INT UNSIGNED;
ALTER TABLE image MODIFY id INT UNSIGNED;
ALTER TABLE imagegroup MODIFY id INT UNSIGNED;
ALTER TABLE instance MODIFY background_id INT UNSIGNED;
ALTER TABLE instance MODIFY id INT UNSIGNED;
ALTER TABLE instance MODIFY master_id INT UNSIGNED;
ALTER TABLE instance MODIFY timeline_id INT UNSIGNED;
ALTER TABLE jobs MODIFY id INT UNSIGNED;
ALTER TABLE language MODIFY id INT UNSIGNED;
ALTER TABLE likes MODIFY id INT UNSIGNED;
ALTER TABLE likes MODIFY post_id INT UNSIGNED;
ALTER TABLE likes MODIFY user_id INT UNSIGNED;
ALTER TABLE master MODIFY id INT UNSIGNED;
ALTER TABLE master_metagroup MODIFY id INT UNSIGNED;
ALTER TABLE master_metagroup MODIFY master_id INT UNSIGNED;
ALTER TABLE master_metagroup MODIFY metagroup_id INT UNSIGNED;
ALTER TABLE media MODIFY group_id INT UNSIGNED;
ALTER TABLE media MODIFY id INT UNSIGNED;
ALTER TABLE media MODIFY master_id INT UNSIGNED;
ALTER TABLE media MODIFY post_id INT UNSIGNED;
ALTER TABLE mediagroup MODIFY id INT UNSIGNED;
ALTER TABLE mediagroup MODIFY master_id INT UNSIGNED;
ALTER TABLE metagroup MODIFY id INT UNSIGNED;
ALTER TABLE post MODIFY id INT UNSIGNED;
ALTER TABLE post MODIFY timeline_id INT UNSIGNED;
ALTER TABLE post MODIFY user_id INT UNSIGNED;
ALTER TABLE post_group MODIFY group_id INT UNSIGNED;
ALTER TABLE post_group MODIFY post_id INT UNSIGNED;
ALTER TABLE post_metagroup MODIFY metaGroup_id INT UNSIGNED;
ALTER TABLE post_metagroup MODIFY post_id INT UNSIGNED;
ALTER TABLE request MODIFY id INT UNSIGNED;
ALTER TABLE session MODIFY id INT UNSIGNED;
ALTER TABLE string MODIFY id INT UNSIGNED;
ALTER TABLE string MODIFY language_id INT UNSIGNED;
ALTER TABLE timeline MODIFY id INT UNSIGNED;
ALTER TABLE usercache MODIFY id INT UNSIGNED;
ALTER TABLE usercache MODIFY instance_id INT UNSIGNED;
ALTER TABLE usercache MODIFY user_id INT UNSIGNED;
ALTER TABLE usercache_metagroup MODIFY metaGroup_id INT UNSIGNED;
ALTER TABLE usercache_metagroup MODIFY user_id INT UNSIGNED;
ALTER TABLE workers MODIFY id INT UNSIGNED;