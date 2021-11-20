export {};

declare global {
  interface Window {
    SENTRY_DSN: string;
    SENTRY_RELEASE: string;
    HAS_MULTIPLE_HOSTS: boolean;
  }

  interface Array<T> {
    move: (from: number, to: number) => T[];
  }
}
