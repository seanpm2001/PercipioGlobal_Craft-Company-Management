<?php

namespace percipiolondon\companymanagement\db;


abstract class Table
{
    const CM_COMPANIES = "{{%companymanagement_company}}";
    const CM_USERS = "{{%companymanagement_users}}";
    const CM_DOCUMENTS = "{{%companymanagement_documents}}";
    const CM_COMPANYTYPES = "{{%companymanagement_companytypes}}";
    const CM_COMPANYTYPES_SITES = "{{%companymanagement_companytypes_sites}}";
    const CM_PERMISSIONS = "{{%companymanagement_permissions}}";
    const CM_PERMISSIONS_USERS = "{{%companymanagement_permissions_users}}";
}
