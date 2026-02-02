-- Seed: Initial Resume Templates
-- Date: 2025-12-26
-- Description: Insert 3 free templates and 2 premium templates

INSERT INTO `resume_templates` (`name`, `slug`, `description`, `category`, `is_premium`, `is_active`, `sort_order`, `template_schema`) VALUES
-- Template 1: Professional (Free)
('Professional', 'professional', 'Clean and professional design perfect for corporate roles', 'Professional', 0, 1, 1, '{
  "sections": ["header", "summary", "experience", "education", "skills"],
  "colors": {
    "primary": "#2563eb",
    "secondary": "#64748b",
    "background": "#ffffff",
    "text": "#1e293b"
  },
  "fonts": {
    "heading": "Arial, sans-serif",
    "body": "Arial, sans-serif"
  },
  "layout": "single-column",
  "header_style": "centered"
}'),

-- Template 2: Modern (Free)
('Modern', 'modern', 'Contemporary design with bold typography', 'Modern', 0, 1, 2, '{
  "sections": ["header", "summary", "experience", "education", "skills", "languages"],
  "colors": {
    "primary": "#0ea57a",
    "secondary": "#64748b",
    "background": "#ffffff",
    "text": "#1e293b"
  },
  "fonts": {
    "heading": "Georgia, serif",
    "body": "Arial, sans-serif"
  },
  "layout": "two-column",
  "header_style": "left-aligned"
}'),

-- Template 3: Classic (Free)
('Classic', 'classic', 'Traditional format with clear sections', 'Classic', 0, 1, 3, '{
  "sections": ["header", "summary", "experience", "education", "skills", "certifications"],
  "colors": {
    "primary": "#1e293b",
    "secondary": "#475569",
    "background": "#ffffff",
    "text": "#0f172a"
  },
  "fonts": {
    "heading": "Times New Roman, serif",
    "body": "Times New Roman, serif"
  },
  "layout": "single-column",
  "header_style": "centered"
}'),

-- Template 4: Creative (Premium)
('Creative', 'creative', 'Eye-catching design for creative professionals', 'Creative', 1, 1, 4, '{
  "sections": ["header", "summary", "experience", "education", "skills", "projects", "achievements"],
  "colors": {
    "primary": "#8b5cf6",
    "secondary": "#a78bfa",
    "background": "#faf5ff",
    "text": "#1e293b"
  },
  "fonts": {
    "heading": "Georgia, serif",
    "body": "Arial, sans-serif"
  },
  "layout": "two-column",
  "header_style": "centered"
}'),

-- Template 5: Executive (Premium)
('Executive', 'executive', 'Sophisticated design for senior roles', 'Professional', 1, 1, 5, '{
  "sections": ["header", "summary", "experience", "education", "skills", "certifications", "achievements"],
  "colors": {
    "primary": "#1e40af",
    "secondary": "#3b82f6",
    "background": "#ffffff",
    "text": "#0f172a"
  },
  "fonts": {
    "heading": "Georgia, serif",
    "body": "Calibri, sans-serif"
  },
  "layout": "single-column",
  "header_style": "left-aligned"
}');

