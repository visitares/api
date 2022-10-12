SELECT
	usi.*,

	-- Category
	c.maxSCore as category_maxScore,

	-- User
	u.id as user_id,
	u.username as user_username,
	u.salutation as user_salutation,
	u.title as user_title,
	u.firstname as user_firstname,
	u.lastname as user_lastname,
	u.email as user_email,
	u.description as user_description

FROM
	usersubmitinstance usi

LEFT JOIN
	category c ON c.id = usi.category_id

LEFT JOIN
	user u ON u.id = usi.user_id

LEFT JOIN
	group_user gu ON gu.user_id = u.id

{where}

GROUP BY
	usi.id

ORDER BY
	usi.creationDate ASC
