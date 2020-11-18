-- Linking plugin

create table if not exists cm_link_org_identity_enrollers
(
    id                   serial        not null
        constraint cm_link_org_identity_enrollers_pkey
            primary key,
    co_id                integer
        constraint cm_link_org_identity_enrollers_co_id_fkey
            references cm_cos(id),
    status               varchar(1)    not null,
    cmp_attribute_name   varchar(80)   not null,
    email_redirect_mode  varchar(1)    not null,
    verification_subject varchar(256)  not null,
    verification_body    varchar(4000) not null,
    introduction_text    varchar(4000) not null,
    logout_endpoint      varchar(80)   not null,
    aux_auth             varchar(80)   not null,
    user_id_attribute    varchar(64)   not null,
    return               varchar(50)   not null,
    exp_window           integer,
    created              timestamp,
    modified             timestamp,
    idp_blacklist        text
);


create table if not exists cm_link_org_identity_eofs
(
    id                            serial    not null
        constraint cm_link_org_identity_eofs_pkey
            primary key,
    co_enrollment_flow_id         integer   not null
        constraint cm_link_org_identity_eofs_co_enrollment_flow_id_fkey
            references cm_co_enrollment_flows(id),
    link_org_identity_enroller_id integer
        constraint cm_link_org_identity_eofs_link_org_identity_enroller_id_fkey
            references cm_link_org_identity_enrollers(id),
    mode                          varchar(1) default NULL::character varying,
    created                       timestamp not null,
    modified                      timestamp
);

create table if not exists cm_link_org_identity_states
(
    id                            serial                not null
        constraint cm_link_org_identity_states_pkey
            primary key,
    link_org_identity_enroller_id integer
        constraint cm_link_org_identity_states_link_org_identity_enroller_id_fkey
            references cm_link_org_identity_enrollers(id),
    token                         varchar(80)           not null,
    data                          varchar(2048)         not null,
    created                       timestamp             not null,
    modified                      timestamp,
    deleted                       boolean default false not null,
    type                          char(2)
);

create index if not exists link_org_identity_states_i1
    on cm_link_org_identity_states (token);