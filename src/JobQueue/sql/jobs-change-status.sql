UPDATE
	jobs

SET
	modificationDate = NOW(),
	status = :status

WHERE
	id IN (%s)
