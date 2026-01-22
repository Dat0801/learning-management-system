export interface Lesson {
  id: number;
  title: string;
  content: string;
  video_url?: string;
  duration?: string;
  is_preview?: boolean;
  order: number;
  is_completed?: boolean;
  has_quiz?: boolean;
}

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Course {
  id: number;
  title: string;
  description: string;
  price: number;
  thumbnail: string;
  instructor_id: number;
  instructor?: User;
  lessons?: Lesson[];
  is_enrolled?: boolean;
  enrollments_count?: number;
}
