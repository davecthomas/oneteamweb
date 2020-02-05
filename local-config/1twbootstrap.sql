--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO postgres;

SET search_path = public, pg_catalog;

--
-- Name: armor(bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION armor(bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_armor';


ALTER FUNCTION public.armor(bytea) OWNER TO postgres;

--
-- Name: crypt(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION crypt(text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_crypt';


ALTER FUNCTION public.crypt(text, text) OWNER TO postgres;

--
-- Name: dearmor(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dearmor(text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_dearmor';


ALTER FUNCTION public.dearmor(text) OWNER TO postgres;

--
-- Name: decrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION decrypt(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_decrypt';


ALTER FUNCTION public.decrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: decrypt_iv(bytea, bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION decrypt_iv(bytea, bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_decrypt_iv';


ALTER FUNCTION public.decrypt_iv(bytea, bytea, bytea, text) OWNER TO postgres;

--
-- Name: digest(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION digest(text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_digest';


ALTER FUNCTION public.digest(text, text) OWNER TO postgres;

--
-- Name: digest(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION digest(bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_digest';


ALTER FUNCTION public.digest(bytea, text) OWNER TO postgres;

--
-- Name: encrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION encrypt(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_encrypt';


ALTER FUNCTION public.encrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: encrypt_iv(bytea, bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION encrypt_iv(bytea, bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_encrypt_iv';


ALTER FUNCTION public.encrypt_iv(bytea, bytea, bytea, text) OWNER TO postgres;

--
-- Name: gen_random_bytes(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION gen_random_bytes(integer) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_random_bytes';


ALTER FUNCTION public.gen_random_bytes(integer) OWNER TO postgres;

--
-- Name: gen_salt(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION gen_salt(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_gen_salt';


ALTER FUNCTION public.gen_salt(text) OWNER TO postgres;

--
-- Name: gen_salt(text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION gen_salt(text, integer) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_gen_salt_rounds';


ALTER FUNCTION public.gen_salt(text, integer) OWNER TO postgres;

--
-- Name: hmac(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION hmac(text, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_hmac';


ALTER FUNCTION public.hmac(text, text, text) OWNER TO postgres;

--
-- Name: hmac(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION hmac(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_hmac';


ALTER FUNCTION public.hmac(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_key_id(bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_key_id(bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_key_id_w';


ALTER FUNCTION public.pgp_key_id(bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt(bytea, bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt(bytea, bytea, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt(bytea, bytea, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt_bytea(bytea, bytea) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt_bytea(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_decrypt_bytea(bytea, bytea, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt(text, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_encrypt(text, bytea) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_text';


ALTER FUNCTION public.pgp_pub_encrypt(text, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt(text, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_encrypt(text, bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_text';


ALTER FUNCTION public.pgp_pub_encrypt(text, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt_bytea(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_encrypt_bytea(bytea, bytea) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_bytea';


ALTER FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt_bytea(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_pub_encrypt_bytea(bytea, bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_bytea';


ALTER FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_decrypt(bytea, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_text';


ALTER FUNCTION public.pgp_sym_decrypt(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_decrypt(bytea, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_text';


ALTER FUNCTION public.pgp_sym_decrypt(bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt_bytea(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_decrypt_bytea(bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_bytea';


ALTER FUNCTION public.pgp_sym_decrypt_bytea(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt_bytea(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_decrypt_bytea(bytea, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_bytea';


ALTER FUNCTION public.pgp_sym_decrypt_bytea(bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_encrypt(text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_text';


ALTER FUNCTION public.pgp_sym_encrypt(text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_encrypt(text, text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_text';


ALTER FUNCTION public.pgp_sym_encrypt(text, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt_bytea(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_encrypt_bytea(bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_bytea';


ALTER FUNCTION public.pgp_sym_encrypt_bytea(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt_bytea(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION pgp_sym_encrypt_bytea(bytea, text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_bytea';


ALTER FUNCTION public.pgp_sym_encrypt_bytea(bytea, text, text) OWNER TO postgres;

--
-- Name: attendance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE attendance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.attendance_id_seq OWNER TO postgres;

--
-- Name: attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('attendance_id_seq', 1, false);


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: attendance; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE attendance (
    memberid integer NOT NULL,
    attendancedate date NOT NULL,
    eventid integer,
    id integer DEFAULT nextval('attendance_id_seq'::regclass) NOT NULL,
    teamid integer,
    "type" integer,

);


ALTER TABLE public.attendance OWNER TO postgres;

--
-- Name: audit_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE audit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.audit_id_seq OWNER TO postgres;

--
-- Name: audit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('audit_id_seq', 1, false);


--
-- Name: audit; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE audit (
    id integer DEFAULT nextval('audit_id_seq'::regclass) NOT NULL
);


ALTER TABLE public.audit OWNER TO postgres;

--
-- Name: coaches_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE coaches_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.coaches_id_seq OWNER TO postgres;

--
-- Name: coaches_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('coaches_id_seq', 1, false);


--
-- Name: customdata_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE customdata_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.customdata_id_seq OWNER TO postgres;

--
-- Name: customdata_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('customdata_id_seq', 1, false);


--
-- Name: customdata; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE customdata (
    id integer DEFAULT nextval('customdata_id_seq'::regclass) NOT NULL,
    customfieldid integer,
    memberid integer,
    valuelist integer,
    valueint integer,
    valuebool boolean,
    valuetext character varying(80),
    valuedate date,
    valuefloat real,
    teamid integer
);


ALTER TABLE public.customdata OWNER TO postgres;

--
-- Name: COLUMN customdata.valuelist; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN customdata.valuelist IS 'references the customlist id';


--
-- Name: customdatatypes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE customdatatypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.customdatatypes_id_seq OWNER TO postgres;

--
-- Name: customdatatypes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('customdatatypes_id_seq', 1, false);


--
-- Name: customdatatypes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE customdatatypes (
    id integer DEFAULT nextval('customdatatypes_id_seq'::regclass) NOT NULL,
    typename character varying(80)
);


ALTER TABLE public.customdatatypes OWNER TO postgres;

--
-- Name: customfields_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE customfields_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.customfields_id_seq OWNER TO postgres;

--
-- Name: customfields_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('customfields_id_seq', 1, false);


--
-- Name: customfields; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE customfields (
    id integer DEFAULT nextval('customfields_id_seq'::regclass) NOT NULL,
    customdatatypeid integer NOT NULL,
    name character varying,
    teamid integer,
    displayconditionobject character varying(80),
    displayconditionfield character varying(80),
    displayconditionoperator character varying(2),
    displayconditionvalue character varying(80),
    hasdisplaycondition boolean,
    listorder integer,
    customlistid integer
);


ALTER TABLE public.customfields OWNER TO postgres;

--
-- Name: COLUMN customfields.displayconditionvalue; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN customfields.displayconditionvalue IS 'this is char data that has to be cast to the data type of the display condition ';


--
-- Name: customlistdata_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE customlistdata_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.customlistdata_id_seq OWNER TO postgres;

--
-- Name: customlistdata_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('customlistdata_id_seq', 1, false);


--
-- Name: customlistdata; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE customlistdata (
    id integer DEFAULT nextval('customlistdata_id_seq'::regclass) NOT NULL,
    customlistid integer,
    listitemname character varying(80),
    listorder integer,
    teamid integer
);


ALTER TABLE public.customlistdata OWNER TO postgres;

--
-- Name: customlists_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE customlists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.customlists_id_seq OWNER TO postgres;

--
-- Name: customlists_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('customlists_id_seq', 1, false);


--
-- Name: customlists; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE customlists (
    id integer DEFAULT nextval('customlists_id_seq'::regclass) NOT NULL,
    name character varying(80),
    teamid integer
);


ALTER TABLE public.customlists OWNER TO postgres;

--
-- Name: epayments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE epayments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.epayments_id_seq OWNER TO postgres;

--
-- Name: epayments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('epayments_id_seq', 1, false);


--
-- Name: epayments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE epayments (
    id integer DEFAULT nextval('epayments_id_seq'::regclass) NOT NULL,
    source integer,
    txid character varying(128),
    reconciled boolean DEFAULT false,
    teamid integer,
    amount numeric(6,2),
    date date,
    item character varying(128),
    payeremail character varying(128),
    skuname character varying(128),
    fee numeric(6,2),
    userid integer
);


ALTER TABLE public.epayments OWNER TO postgres;

--
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.events_id_seq OWNER TO postgres;

--
-- Name: events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('events_id_seq', 1, true);


--
-- Name: events; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE events (
    id integer DEFAULT nextval('events_id_seq'::regclass) NOT NULL,
    name character varying(80) NOT NULL,
    eventdate date,
    location character varying(80),
    listorder integer,
    teamid integer,
    scannable boolean,
    programid integer
);


ALTER TABLE public.events OWNER TO postgres;

--
-- Name: feedback_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE feedback_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.feedback_id_seq OWNER TO postgres;

--
-- Name: feedback_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('feedback_id_seq', 1, false);


--
-- Name: feedback; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE feedback (
    id integer DEFAULT nextval('feedback_id_seq'::regclass) NOT NULL,
    summary character varying(64),
    detail character varying(512),
    userid integer,
    datefeedback date,
    teamid integer
);


ALTER TABLE public.feedback OWNER TO postgres;

--
-- Name: images_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.images_id_seq OWNER TO postgres;

--
-- Name: images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('images_id_seq', 1, true);


--
-- Name: images; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE images (
    id integer DEFAULT nextval('images_id_seq'::regclass) NOT NULL,
    url character varying(255),
    filename character varying(255),
    teamid integer,
    type integer,
    objid integer
);


ALTER TABLE public.images OWNER TO postgres;

--
-- Name: levels_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE levels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.levels_id_seq OWNER TO postgres;

--
-- Name: SEQUENCE levels_id_seq; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON SEQUENCE levels_id_seq IS 'For generating new levels IDs';


--
-- Name: levels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('levels_id_seq', 1, true);


--
-- Name: levels; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE levels (
    id integer DEFAULT nextval('levels_id_seq'::regclass) NOT NULL,
    name character varying(50),
    programid integer,
    listorder integer,
    teamid integer
);


ALTER TABLE public.levels OWNER TO postgres;

--
-- Name: COLUMN levels.programid; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN levels.programid IS 'The program that this level is relevant for. ';


--
-- Name: order_orderitems_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE order_orderitems_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.order_orderitems_id_seq OWNER TO postgres;

--
-- Name: order_orderitems_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('order_orderitems_id_seq', 1, false);


--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO postgres;

--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('payments_id_seq', 1, false);

-- Sequence: redemptioncard_id_seq

-- DROP SEQUENCE redemptioncard_id_seq;

CREATE SEQUENCE redemptioncard_id_seq
  INCREMENT BY 1
  MINVALUE 1
  NO MAXVALUE
  START WITH 1
  CACHE 1;
ALTER TABLE redemptioncard_id_seq OWNER TO postgres;

--
-- Name: orderitems; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE orderitems (
    id integer DEFAULT nextval('payments_id_seq'::regclass) NOT NULL,
    programid integer,
    paymentdate date,
    userid integer,
    teamid integer,
    paymentmethod integer,
    amount numeric(6,2),
    skuid integer,
    numeventsremaining integer,
    fee numeric(6,2),
    ispaid boolean,
    isrefunded boolean,
    orderid integer
);


ALTER TABLE public.orderitems OWNER TO postgres;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.orders_id_seq OWNER TO postgres;

--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('orders_id_seq', 1, false);


--
-- Name: orders; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE orders (
    id integer DEFAULT nextval('orders_id_seq'::regclass) NOT NULL,
    userid integer,
    teamid integer,
    orderdate date,
    duedate date,
    discount numeric(6,2),
    ispaid boolean DEFAULT false,
    paymentmethod integer
);


ALTER TABLE public.orders OWNER TO postgres;

--
-- Name: paymentmethods; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE paymentmethods (
    id integer NOT NULL,
    name character varying(50),
    teamid integer,
    listorder integer
);


ALTER TABLE public.paymentmethods OWNER TO postgres;

--
-- Name: paymentmethods_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE paymentmethods_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.paymentmethods_id_seq OWNER TO postgres;

--
-- Name: paymentmethods_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE paymentmethods_id_seq OWNED BY paymentmethods.id;


--
-- Name: paymentmethods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('paymentmethods_id_seq', 1, false);


--
-- Name: profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE profiles_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.profiles_id_seq OWNER TO postgres;

--
-- Name: profiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('profiles_id_seq', 1, false);


--
-- Name: profiles; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE profiles (
    profilename character varying(80) NOT NULL,
    productname character varying(80) NOT NULL,
    logofile character varying(255),
    cssfile character varying(255),
    id integer DEFAULT nextval('profiles_id_seq'::regclass) NOT NULL,
    sessiontimeout integer DEFAULT 20,
    activityname character varying(80),
    membertitle character varying DEFAULT 80
);


ALTER TABLE public.profiles OWNER TO postgres;

--
-- Name: programs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE programs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.programs_id_seq OWNER TO postgres;

--
-- Name: programs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('programs_id_seq', 1, true);


--
-- Name: programs; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE programs (
    id integer DEFAULT nextval('programs_id_seq'::regclass) NOT NULL,
    name character varying(80) NOT NULL,
    teamid integer,
    listorder integer,
    eventid integer
);


ALTER TABLE public.programs OWNER TO postgres;

--
-- Name: promotions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE promotions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.promotions_id_seq OWNER TO postgres;

--
-- Name: promotions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('promotions_id_seq', 1, true);


--
-- Name: promotions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE promotions (
    id integer DEFAULT nextval('promotions_id_seq'::regclass) NOT NULL,
    memberid integer,
    promotiondate date,
    newlevel integer,
    teamid integer,
    imageid integer
);


ALTER TABLE public.promotions OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('roles_id_seq', 1, false);


--
-- Name: sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE sessions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.sessions_id_seq OWNER TO postgres;

--
-- Name: sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('sessions_id_seq', 1, true);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE sessions (
    id integer DEFAULT nextval('sessions_id_seq'::regclass) NOT NULL,
    ipaddr character(16),
    userid integer,
    sessionkey character(8),
    timecreated timestamp with time zone,
    timeexpires timestamp with time zone,
    login character varying(50),
    roleid integer,
    fullname character varying(50),
    teamid integer,
    isbillable boolean,
    status integer,
    authsms numeric,
    authsmsretries numeric
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: skus_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE skus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.skus_id_seq OWNER TO postgres;

--
-- Name: skus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('skus_id_seq', 1, false);


--
-- Name: skus; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE skus (
    id integer DEFAULT nextval('skus_id_seq'::regclass) NOT NULL,
    name character varying(128),
    programid integer,
    listorder integer,
    teamid integer,
    price numeric(6,2),
    description character varying(1028),
    numevents integer,
    expires interval
);


ALTER TABLE public.skus OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    firstname character varying(50),
    lastname character varying(50),
    startdate date,
    address character varying(254),
    city character varying(50),
    state character varying(20),
    postalcode character varying(20),
    smsphone character varying(30),
    phone2 character varying(30),
    login character varying(50) NOT NULL,
    birthdate date,
    referredby character varying(50),
    notes text,
    coachid integer,
    emergencycontact character varying(50),
    ecphone1 character varying(50),
    ecphone2 character varying(30),
    gender character(1),
    stopdate date,
    stopreason character varying(80),
    teamid integer,
    roleid integer,
    address2 character varying(80),
    useraccountinfo integer,
    salt character(9),
    passwd character varying(64),
    imageid integer,
    smsphonecarrier character varying(48),
    ipaddr character(16),
    timelockoutexpires timestamp with time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: students_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE students_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.students_id_seq OWNER TO postgres;

--
-- Name: students_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE students_id_seq OWNED BY users.id;


--
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('students_id_seq', 1, true);


--
-- Name: teamaccountinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE teamaccountinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.teamaccountinfo_id_seq OWNER TO postgres;

--
-- Name: teamaccountinfo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('teamaccountinfo_id_seq', 1, true);


--
-- Name: teamaccountinfo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE teamaccountinfo (
    id integer DEFAULT nextval('teamaccountinfo_id_seq'::regclass) NOT NULL,
    teamid integer,
    status integer,
    plan integer,
    planduration integer,
    isbillable boolean
);


ALTER TABLE public.teamaccountinfo OWNER TO postgres;

--
-- Name: teampayments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE teampayments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.teampayments_id_seq OWNER TO postgres;

--
-- Name: teampayments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('teampayments_id_seq', 1, false);


--
-- Name: teampayments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE teampayments (
    id integer DEFAULT nextval('teampayments_id_seq'::regclass) NOT NULL,
    paymentdate date,
    teamid integer,
    payment numeric(6,2)
);


ALTER TABLE public.teampayments OWNER TO postgres;

--
-- Name: teams_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE teams_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.teams_id_seq OWNER TO postgres;

--
-- Name: teams_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('teams_id_seq', 1, true);


--
-- Name: teams; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE teams (
    id integer DEFAULT nextval('teams_id_seq'::regclass) NOT NULL,
    name character varying(80),
    city character varying(80),
    coachid integer,
    state character varying(2),
    address1 character varying(80),
    address2 character varying(80),
    postalcode character varying(80),
    phone character varying(80),
    email character varying(80),
    website character varying(80),
    adminid integer,
    activityname character varying(80),
    notes character varying(180),
    startdate date,
    logofile character varying(255),
    paymenturl character varying(128),
    eventidattendance integer DEFAULT 1,
    imageid integer,
    introtext character varying(1024)
);


ALTER TABLE public.teams OWNER TO postgres;

--
-- Name: teamterms_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE teamterms_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.teamterms_id_seq OWNER TO postgres;

--
-- Name: teamterms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('teamterms_id_seq', 1, false);


--
-- Name: teamterms; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE teamterms (
    id integer DEFAULT nextval('teamterms_id_seq'::regclass) NOT NULL,
    termteam character varying(32),
    termuser character varying(32),
    termadmin character varying,
    termcoach character varying(32),
    termmember character varying(32),
    teamid integer,
    termclass character varying(32)
);


ALTER TABLE public.teamterms OWNER TO postgres;

--
-- Name: useraccountinfo_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE useraccountinfo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.useraccountinfo_id_seq OWNER TO postgres;

--
-- Name: useraccountinfo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('useraccountinfo_id_seq', 1, true);


--
-- Name: useraccountinfo; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE useraccountinfo (
    id integer DEFAULT nextval('useraccountinfo_id_seq'::regclass) NOT NULL,
    email character varying(80),
    status integer DEFAULT 1,
    userid integer,
    teamid integer,
    isbillable boolean DEFAULT true
);


ALTER TABLE public.useraccountinfo OWNER TO postgres;

--
-- Name: recognizeduserlocations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE recognizeduserlocations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.recognizeduserlocations_id_seq OWNER TO postgres;

--
-- Name: recognizeduserlocations; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE recognizeduserlocations (
    id integer DEFAULT nextval('recognizeduserlocations_id_seq'::regclass) NOT NULL,
    userid integer NOT NULL,
    teamid integer,
    ipaddr character(16) NOT NULL
);


ALTER TABLE public.recognizeduserlocations OWNER TO postgres;

--
-- Name: recognizeduserlocations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY recognizeduserlocations
    ADD CONSTRAINT recognizeduserlocations_pkey PRIMARY KEY (id);

-- Table: redemptioncards

-- DROP TABLE redemptioncards;

CREATE TABLE redemptioncards
(
  id integer NOT NULL DEFAULT nextval('redemptioncard_id_seq'::regclass),
  teamid integer,
  userid integer,
  skuid integer,
  createdate date,
  amountpaid numeric(6,2),
  numeventsremaining integer,
  expires date,
  paymentmethod integer DEFAULT 0,
  description character varying(128),
  "type" integer,
  facevalue numeric(6,2),
  code character(12),
  CONSTRAINT redemptioncards_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE redemptioncards OWNER TO postgres;

-- Table: attendanceconsoles

-- DROP TABLE attendanceconsoles;

CREATE TABLE attendanceconsoles
(
  id serial NOT NULL,
  "name" character varying(64),
  ip character varying(16) NOT NULL,
  teamid integer NOT NULL,
  CONSTRAINT attendanceconsoles_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE attendanceconsoles OWNER TO postgres;

-- Sequence: attendanceconsoles_id_seq

-- DROP SEQUENCE attendanceconsoles_id_seq;

CREATE SEQUENCE attendanceconsoles_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE attendanceconsoles_id_seq OWNER TO postgres;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE paymentmethods ALTER COLUMN id SET DEFAULT nextval('paymentmethods_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('students_id_seq'::regclass);


--
-- Data for Name: attendance; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY attendance (memberid, attendancedate, eventid, id, teamid) FROM stdin;
\.


--
-- Data for Name: audit; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY audit (id) FROM stdin;
\.


--
-- Data for Name: customdata; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY customdata (id, customfieldid, memberid, valuelist, valueint, valuebool, valuetext, valuedate, valuefloat, teamid) FROM stdin;
\.


--
-- Data for Name: customdatatypes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY customdatatypes (id, typename) FROM stdin;
2	Whole number
3	Floating point number
4	Boolean (Yes, No)
5	Date
1	Text (up to 80 characters)
7	List
\.


--
-- Data for Name: customfields; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY customfields (id, customdatatypeid, name, teamid, displayconditionobject, displayconditionfield, displayconditionoperator, displayconditionvalue, hasdisplaycondition, listorder, customlistid) FROM stdin;
\.


--
-- Data for Name: customlistdata; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY customlistdata (id, customlistid, listitemname, listorder, teamid) FROM stdin;
\.


--
-- Data for Name: customlists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY customlists (id, name, teamid) FROM stdin;
\.


--
-- Data for Name: epayments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY epayments (id, source, txid, reconciled, teamid, amount, date, item, payeremail, skuname, fee, userid) FROM stdin;
\.


--
-- Data for Name: events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY events (id, name, eventdate, location, listorder, teamid, scannable, programid) FROM stdin;
\.


--
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY feedback (id, summary, detail, userid, datefeedback, teamid) FROM stdin;
\.


--
-- Data for Name: images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY images (id, url, filename, teamid, type, objid) FROM stdin;
\.


--
-- Data for Name: levels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY levels (id, name, programid, listorder, teamid) FROM stdin;
\.


--
-- Data for Name: orderitems; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY orderitems (id, programid, paymentdate, userid, teamid, paymentmethod, amount, skuid, numeventsremaining, fee, ispaid, isrefunded, orderid) FROM stdin;
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY orders (id, userid, teamid, orderdate, duedate, discount, ispaid, paymentmethod) FROM stdin;
\.


--
-- Data for Name: paymentmethods; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY paymentmethods (id, name, teamid, listorder) FROM stdin;
\.


--
-- Data for Name: profiles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY profiles (profilename, productname, logofile, cssfile, id, sessiontimeout, activityname, membertitle) FROM stdin;
\.


--
-- Data for Name: programs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY programs (id, name, teamid, listorder, eventid) FROM stdin;
\.


--
-- Data for Name: promotions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY promotions (id, memberid, promotiondate, newlevel, teamid, imageid) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY sessions (id, ipaddr, userid, sessionkey, timecreated, timeexpires, login, roleid, fullname, teamid, isbillable, status, authsms, authsmsretries) FROM stdin;
\.


--
-- Data for Name: skus; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY skus (id, name, programid, listorder, teamid, price, description, numevents, expires) FROM stdin;
\.


--
-- Data for Name: teamaccountinfo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY teamaccountinfo (id, teamid, status, plan, planduration, isbillable) FROM stdin;
\.


--
-- Data for Name: teampayments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY teampayments (id, paymentdate, teamid, payment) FROM stdin;
\.


--
-- Data for Name: teams; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY teams (id, name, city, coachid, state, address1, address2, postalcode, phone, email, website, adminid, activityname, notes, startdate, logofile, paymenturl, eventidattendance, imageid, introtext) FROM stdin;
\.


--
-- Data for Name: teamterms; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY teamterms (id, termteam, termuser, termadmin, termcoach, termmember, teamid, termclass) FROM stdin;
\.


--
-- Data for Name: useraccountinfo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY useraccountinfo (id, email, status, userid, teamid, isbillable) FROM stdin;
1	admin@1teamweb.com	1	1	0	f
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY users (id, firstname, lastname, startdate, address, city, state, postalcode, smsphone, phone2, login, birthdate, referredby, notes, coachid, emergencycontact, ecphone1, ecphone2, gender, stopdate, stopreason, teamid, roleid, address2, useraccountinfo, salt, passwd, imageid, smsphonecarrier, ipaddr, timelockoutexpires) FROM stdin;
1	App 	Admin	2003-01-01	123 Main	Austin	TX	78746	(512) 423-1010		admin@1teamweb.com	\N			\N				 	\N	\N	\N	1	\N	1	f417895de	9d54cdc70b595ffb223078ba3b0e42a26b57d55c	\N	verizon	\N	\N
\.


--
-- Name: PaymentMethod_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY paymentmethods
    ADD CONSTRAINT "PaymentMethod_pkey" PRIMARY KEY (id);


--
-- Name: attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY attendance
    ADD CONSTRAINT attendance_pkey PRIMARY KEY (id);


--
-- Name: audit_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY audit
    ADD CONSTRAINT audit_pkey PRIMARY KEY (id);


--
-- Name: customdata_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY customdata
    ADD CONSTRAINT customdata_pkey PRIMARY KEY (id);


--
-- Name: customfields_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY customfields
    ADD CONSTRAINT customfields_pkey PRIMARY KEY (id);


--
-- Name: customlists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY customlistdata
    ADD CONSTRAINT customlists_pkey PRIMARY KEY (id);


--
-- Name: customlists_pkey1; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY customlists
    ADD CONSTRAINT customlists_pkey1 PRIMARY KEY (id);


--
-- Name: datatypes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY customdatatypes
    ADD CONSTRAINT datatypes_pkey PRIMARY KEY (id);


--
-- Name: epayments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY epayments
    ADD CONSTRAINT epayments_pkey PRIMARY KEY (id);


--
-- Name: events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- Name: feedback_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY feedback
    ADD CONSTRAINT feedback_pkey PRIMARY KEY (id);


--
-- Name: id; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY levels
    ADD CONSTRAINT id PRIMARY KEY (id);


--
-- Name: images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY images
    ADD CONSTRAINT images_pkey PRIMARY KEY (id);


--
-- Name: invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY orders
    ADD CONSTRAINT invoices_pkey PRIMARY KEY (id);


--
-- Name: login_unique; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT login_unique UNIQUE (login);


--
-- Name: payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY orderitems
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY profiles
    ADD CONSTRAINT profiles_pkey PRIMARY KEY (id);


--
-- Name: programs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY programs
    ADD CONSTRAINT programs_pkey PRIMARY KEY (id);


--
-- Name: promotions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY promotions
    ADD CONSTRAINT promotions_pkey PRIMARY KEY (id);


--
-- Name: sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: skus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY skus
    ADD CONSTRAINT skus_pkey PRIMARY KEY (id);


--
-- Name: students-pg_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT "students-pg_pkey" PRIMARY KEY (id);


--
-- Name: teamaccountinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY teamaccountinfo
    ADD CONSTRAINT teamaccountinfo_pkey PRIMARY KEY (id);


--
-- Name: teampayments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY teampayments
    ADD CONSTRAINT teampayments_pkey PRIMARY KEY (id);


--
-- Name: teams_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: teamterms_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY teamterms
    ADD CONSTRAINT teamterms_pkey PRIMARY KEY (id);


--
-- Name: useraccountinfo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY useraccountinfo
    ADD CONSTRAINT useraccountinfo_pkey PRIMARY KEY (id);


--
-- Name: users_login_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_login_key UNIQUE (login);


--
-- Name: feedback_userid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY feedback
    ADD CONSTRAINT feedback_userid_fkey FOREIGN KEY (userid) REFERENCES users(id);


--
-- Name: payments_skuid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY orderitems
    ADD CONSTRAINT payments_skuid_fkey FOREIGN KEY (skuid) REFERENCES skus(id);


--
-- Name: skus_programid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY skus
    ADD CONSTRAINT skus_programid_fkey FOREIGN KEY (programid) REFERENCES programs(id);


--
-- Name: skus_teamid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY skus
    ADD CONSTRAINT skus_teamid_fkey FOREIGN KEY (teamid) REFERENCES teams(id);


--
-- Name: teamaccountinfo_teamid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY teamaccountinfo
    ADD CONSTRAINT teamaccountinfo_teamid_fkey FOREIGN KEY (teamid) REFERENCES teams(id);


--
-- Name: useraccountinfo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT useraccountinfo FOREIGN KEY (useraccountinfo) REFERENCES useraccountinfo(id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

