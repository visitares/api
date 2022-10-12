SELECT
	{columns}

FROM
	user

{joins}

WHERE
	{where}

GROUP BY
	user.id

{having}

ORDER BY
	{orderBy}

LIMIT {offset}, {limit}