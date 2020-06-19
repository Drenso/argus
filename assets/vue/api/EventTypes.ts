export interface EventStats {
  all: number;
  fully_handled: number;
  unhandled: number;
  partially_handled: number;
}

export interface TimedEventStats {
  last_hour: EventStats;
  last_day: EventStats;
  last_week: EventStats;
  last_month: EventStats;
  last_year: EventStats;
}
