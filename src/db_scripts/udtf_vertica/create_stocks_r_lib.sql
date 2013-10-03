DROP LIBRARY stocks_lib CASCADE;

\set libfile '\''`pwd`'/stocks_udtf_r_lib.R\''
CREATE LIBRARY stocks_lib AS :libfile LANGUAGE 'R';

CREATE OR REPLACE TRANSFORM FUNCTION DetectSolavancoInterval
AS LANGUAGE 'R' NAME 'DetectSolavancoIntervalFactory' LIBRARY stocks_lib;
