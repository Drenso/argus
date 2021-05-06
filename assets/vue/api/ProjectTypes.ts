export interface Project {
  id: number;
  name: string;
  last_event: string;
  environments: ProjectEnvironment[];

  // Virtual
  current_state: ProjectEnvironmentState;

  // Depending on serialization groups
  _gitlab_url?: string;
}

export interface ProjectEnvironment {
  id: number;
  name: string;
  current_state: ProjectEnvironmentState;
  last_event: string;
}

export type ProjectEnvironmentState = 'ok' | 'unknown' | 'running' | 'failed';
