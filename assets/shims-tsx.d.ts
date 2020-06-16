export {};

declare global {
  interface Window {
    SENTRY_DSN: string;
    SENTRY_RELEASE: string;
  }

  interface Array<T> {
    move: (from: number, to: number) => T[];
  }
}
