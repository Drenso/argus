export interface Project {
  id: number;
  name: string;
  last_event: string;

  // Depending on serialization groups
  _gitlab_url?: string;
}
