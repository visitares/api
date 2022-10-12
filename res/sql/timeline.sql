SELECT DISTINCT
	p.*,
	i.id AS instanceId,
	i.token AS instanceToken,
	user.user_id AS userId,
	user_instance.token AS userInstanceToken

FROM
	post p

LEFT JOIN
	usercache user ON user.id = p.user_id

LEFT JOIN
  instance user_instance ON user_instance.id = user.instance_id

LEFT JOIN
	post_group pg ON pg.post_id = p.id

LEFT JOIN
	groupcache cachedgroup ON cachedgroup.id = pg.group_id

LEFT JOIN
	post_metagroup pmg ON pmg.post_id = p.id

LEFT JOIN
	timeline t ON t.id = p.timeline_id

LEFT JOIN
	instance i ON i.timeline_id = t.id

WHERE
	(
		(user.user_id = :user AND i.id = :instance) OR						-- my own posts
		(p.published = 1 AND i.master_id = :master) OR						-- published on my master (global)
		(p.published = 2 AND i.id = :instance) OR									-- published on my instance
		(p.published = 3 AND pg.group_id IN (:groups)) OR					-- published on my groups
		(p.published = 4 AND p.timeline_id = :timeline) OR				-- published on my timeline
		(p.published = 5 AND pmg.metaGroup_id IN (:metaGroups))		-- published on my metagroups
	)
	{where}

GROUP BY
	p.id

ORDER BY
	p.creationDate DESC

LIMIT :offset, :limit