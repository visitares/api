SELECT
	j.id,
	j.type

FROM
	jobs j

WHERE
	j.type = :type
	AND j.status = 0
	AND (j.sleepUntil IS NULL OR j.sleepUntil <= NOW())
	AND (j.expiresOn IS NULL OR j.expiresOn < NOW())

ORDER BY
	j.priority DESC,
	j.creationDate ASC

LIMIT
	0, %d
