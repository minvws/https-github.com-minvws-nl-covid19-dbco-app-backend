/**
 * Generated by orval v6.13.1 🍺
 * Do not edit manually.
 * DBCO Portal
 * API used for the portal for healthcare (BCO) workers
 * OpenAPI spec version: 1.0.0
 */
import type { FormData } from './formData';
import type { FormCollectionData } from './formCollectionData';
import type { Url } from './url';

export type FormRootData = (FormData & {
  /** An URL that should return the `FormConfig` object for this form. */
  $config: Url;
  /** An object with links to other forms that may be referred by the uiSchema (to be opened in a modal) */
  $forms?: unknown;
}) | (FormCollectionData & {
  /** An URL that should return the `FormConfig` object for this form. */
  $config: Url;
  /** An object with links to other forms that may be referred by the uiSchema (to be opened in a modal) */
  $forms?: unknown;
});
