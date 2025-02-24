/**
 * Generated by orval v6.13.1 🍺
 * Do not edit manually.
 * DBCO Portal
 * API used for the portal for healthcare (BCO) workers
 * OpenAPI spec version: 1.0.0
 */
import type { JsonSchemaDraft07 } from './jsonSchemaDraft07';
import type { UiSchema } from './uiSchema';

export interface FormConfig {
  /** A JSON schema (Draft 07) describing the data that is used for the form. 
Even though the actual data will be FormData or FormCollectionData, these 
meta data properties should not be included in this dataSchema.
This JSON schema is used for referencing the data in the uiSchema.
And to provide frontend (JSON Schema) validation on the form data.
 */
  dataSchema: JsonSchemaDraft07;
  uiSchema: UiSchema;
}
