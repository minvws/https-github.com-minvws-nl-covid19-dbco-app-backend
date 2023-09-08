# Audit Event • Validation • Instructions

Audit events raised by the portal are imported by a third party. Any changes in both structure and contents need to be detected
to enable validation of and communication about these changes. Two separate validator services each monitor a specific type of change:
1. `\App\Services\Audit\AuditEventSchemaValidator` checks for structural changes
2. `\App\Services\Audit\AuditEventSpecValidator` checks for specification changes (i.e. what type of information does the payload contain)

## Feature flags

In case of a validation failure, feature flags determine how the application handles this.
1. `validate_audit_event_schema_enabled` and `validate_audit_event_spec_enabled`
    - These are the main switches. When set to `false`, no validation is performed. The flags below only function if this flag is set to `true`.
2. `suppress_audit_event_schema_error_enabled` and `suppress_audit_event_spec_error_enabled`
    - When set to `true`, validation errors are not thrown.
3. `measure_audit_event_schema_failure_enabled` and `measure_audit_event_spec_failure_enabled`
    - When set to `true`, validation errors are measured and sent to Prometheus.
4. `create_draft_audit_event_schema_enabled`
    - When set to `true`, if an audit event does not match the schema, a draft schema is created to assist the diffing process and determine the desired solution.
5. `update_audit_event_spec_enabled`
    - When set to `true`, if an audit event does not match the specification, the specification CSV file is updated to reflect the latest state of the audit event.

N.B. a warning is always logged when a validation error occurs.

## Structural changes

Structural changes are detected by validating the audit event JSON payload against a JSON schema.
This schema is located at `./schema/audit-event.schema.json`.

## Specification changes

Specification changes are detected by storing these specifications in a CSV file and comparing the latest state of an audit event against it.
This CSV is located at `./specification/audit-event-specification.csv`.

## Fixing schema validation errors

There are two ways to find the deviation(s) between the audit event and the JSON schema:
1. Using the draft JSON schema
2. Using application logs

The first option requires a few more steps but also provides better insight.

### Using draft JSON schema

- Enable the `create_draft_audit_event_schema_enabled` feature by defining an environment variable `CREATE_DRAFT_AUDIT_EVENT_SCHEMA_ENABLED=true`.
- Next, you raise the audit event by visiting a URL that will trigger it or manually, for instance by using an automated test or Tinker.
- Find the difference(s) by diffing the published JSON schema with the new draft JSON schema in `./schema/draft/`.
- Is the structure of the deviating audit event desirable? Then update the published JSON schema to also allow for this structure.
- If not, change the audit event declaration to comply with the published schema definition.

**Don't forget: the main feature flag `validate_audit_event_spec_enabled` must also be enabled.**

### Using application logs

- Find the deviation(s) in the application logs by searching for warnings containing `AuditEvent JSON deviates from schema definition`.
- Is the structure of the deviating audit event desirable? Then update the published JSON schema to also allow for this structure.
- If not, change the audit event declaration to comply with the published schema definition.

## Fixing specification validation errors

- Enable the `update_audit_event_spec_enabled` feature by defining an environment variable `UPDATE_AUDIT_EVENT_SPEC_ENABLED=true`.
- Next, you raise the audit event by visiting a URL that will trigger it or manually, for instance: by using an automated test or Tinker.
- Locate any changes in the specification CSV file in your git working tree.
- Is this latest state desirable? Then commit the changed specification CSV file.
- If not, change the audit event declaration to reflect the specification in the CSV file.

**Don't forget: the main feature flag `validate_audit_event_spec_enabled` must also be enabled.**
