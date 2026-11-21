sudo -u postgres psql

\l      -- vérifier que les 2 bases existent
\du     -- vérifier que les 4 rôles existent

DROP DATABASE IF EXISTS barri_shopdropdown_db WITH (FORCE);

DROP ROLE IF EXISTS barri_shopdropdown_user;
DROP ROLE IF EXISTS barri_shopdropdown_admin;

\l      -- les 2 bases ont disparu
\du     -- les 4 rôles ont disparu 


sudo -u postgres psql


-- 1) Rôles
CREATE ROLE barri_shopdropdown_admin LOGIN PASSWORD '<MDP_ADMIN_TECHNOVA>'
  NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
CREATE ROLE barri_shopdropdown_user LOGIN PASSWORD '<MDP_USER_TECHNOVA>'
  NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
 
-- 2) Base de données (propriétaire = super admin de la base)
CREATE DATABASE barri_shopdropdown_db OWNER barri_shopdropdown_admin;
REVOKE ALL ON DATABASE barri_shopdropdown_db FROM PUBLIC;
GRANT CONNECT ON DATABASE barri_shopdropdown_db TO barri_shopdropdown_user;
 
-- 3) Droits dans la base
\c barri_shopdropdown_db
ALTER SCHEMA public OWNER TO barri_shopdropdown_admin;
REVOKE CREATE ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO barri_shopdropdown_user;

GRANT DELETE ON TABLE sessions, cache, cache_locks, jobs, failed_jobs TO barri_shopdropdown_user;
 
-- Objets existants
GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO barri_shopdropdown_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO barri_shopdropdown_user;
 
-- Objets futurs (créés par le super admin, ex. migrations)
ALTER DEFAULT PRIVILEGES FOR ROLE barri_shopdropdown_admin IN SCHEMA public
  GRANT SELECT, INSERT, UPDATE ON TABLES TO barri_shopdropdown_user;
ALTER DEFAULT PRIVILEGES FOR ROLE barri_shopdropdown_admin IN SCHEMA public
  GRANT USAGE, SELECT ON SEQUENCES TO barri_shopdropdown_user;
 
-- 4) Contrôle : doit afficher les droits par défaut de barri_shopdropdown_admin
\ddp
