SELECT
	w.id,
	w.type,
	w.maxInstances AS max,
	IF(open.total IS NULL, 0, open.total) AS open,
	IF(enqueued.total IS NULL, 0, enqueued.total) AS enqueued,
	IF(running.total IS NULL, 0, running.total) AS running

FROM
	workers w

LEFT JOIN (

	SELECT
		w.id as workerId,
		COUNT(j.id) AS total

	FROM
		jobs j

	LEFT JOIN
		workers w ON w.type = j.type

	WHERE
		j.status = 0
    AND (j.sleepUntil IS NULL OR j.sleepUntil <= NOW())
    AND (j.expiresOn IS NULL OR j.expiresOn < NOW())

	GROUP BY
		w.id

) open ON open.workerId = w.id

LEFT JOIN (

	SELECT
		w.id as workerId,
		COUNT(j.id) AS total

	FROM
		jobs j

	LEFT JOIN
		workers w ON w.type = j.type

	WHERE
		j.status = 1

	GROUP BY
		w.id

) enqueued ON enqueued.workerId = w.id

LEFT JOIN (

	SELECT
		w.id as workerId,
		COUNT(j.id) AS total

	FROM
		jobs j

	LEFT JOIN
		workers w ON w.type = j.type

	WHERE
		j.status = 2

	GROUP BY
		w.id

) running ON running.workerId = w.id
