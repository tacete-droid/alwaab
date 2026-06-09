<?php

return [
    'image_prompt_suffix' => 'high quality, 8k resolution, professional photography, cinematic lighting',
    'video_prompt_suffix' => 'highly detailed, cinematic, smooth motion, 1080p, realistic textures',
    'video_reference_suffix' => 'Recreate a similar concept inspired by the uploaded reference video: matching mood, pacing, composition, and visual style while applying the user request.',
    'vision_model' => env('AI_STUDIO_VISION_MODEL', 'gpt-4o-mini'),

    'text_model' => env('AI_STUDIO_TEXT_MODEL', 'gpt-4o-mini'),
    'image_model' => env('AI_STUDIO_IMAGE_MODEL', 'dall-e-3'),
    'image_size' => env('AI_STUDIO_IMAGE_SIZE', '1024x1024'),

    'video_provider' => env('AI_STUDIO_VIDEO_PROVIDER', 'luma'),
    'luma_api_key' => env('LUMA_API_KEY'),
    'luma_api_url' => env('LUMA_API_URL', 'https://api.lumalabs.ai/dream-machine/v1/generations'),
    'runway_api_key' => env('RUNWAY_API_KEY'),

    'default_credits' => (int) env('AI_STUDIO_DEFAULT_CREDITS', 10),
    'credit_costs' => [
        'text' => 0,
        'image' => 1,
        'video' => 5,
    ],
];
