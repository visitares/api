for file in ./migrations/config/visitares_*.php; do bin/phinx rollback -t $1 -c $file; done;