import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

SCREEN_DIR = r"e:\Projects\ibo\spi\assets\img\screenshots"
os.makedirs(SCREEN_DIR, exist_ok=True)

BRAND_LOGO_PATH = r"e:\Projects\ibo\spi\assets\img\brand_full.png"
BANNER_PATH = r"e:\Projects\ibo\spi\assets\img\banner.png"
ICON_PATH = r"e:\Projects\ibo\spi\assets\img\icon.png"

WALL_CINEMA = r"e:\Projects\ibo\DreamaniaPlayer\app\src\main\res\drawable\bg_wallpaper_cinema.jpg"
WALL_NEON = r"e:\Projects\ibo\DreamaniaPlayer\app\src\main\res\drawable\bg_wallpaper_neon.jpg"
WALL_SPORTS = r"e:\Projects\ibo\DreamaniaPlayer\app\src\main\res\drawable\bg_wallpaper_sports.jpg"
WALL_TECH = r"e:\Projects\ibo\DreamaniaPlayer\app\src\main\res\drawable\bg_wallpaper_tech.jpg"

THEME_18 = r"e:\Projects\ibo\4ibo\img\theme_18.png"
THEME_6 = r"e:\Projects\ibo\4ibo\img\theme_6.png"
THEME_10 = r"e:\Projects\ibo\4ibo\img\theme_10.png"
THEME_12 = r"e:\Projects\ibo\4ibo\img\theme_12.png"

FONT_BOLD = r"C:\Windows\Fonts\segoeuib.ttf"
FONT_REG = r"C:\Windows\Fonts\segoeui.ttf"

def get_font(size, bold=False):
    path = FONT_BOLD if bold else FONT_REG
    try:
        return ImageFont.truetype(path, size)
    except:
        return ImageFont.load_default()

def draw_heart(draw, center_x, center_y, size=16, fill=(239, 68, 68), outline=None):
    points = []
    for t in [i * 0.1 for i in range(63)]:
        x = 16 * (math.sin(t) ** 3)
        y = -(13 * math.cos(t) - 5 * math.cos(2*t) - 2 * math.cos(3*t) - math.cos(4*t))
        px = center_x + x * (size / 28.0)
        py = center_y + y * (size / 28.0)
        points.append((px, py))
    draw.polygon(points, fill=fill, outline=outline)

def draw_star(draw, center_x, center_y, size=14, fill=(245, 158, 11)):
    points = []
    for i in range(10):
        angle = i * (math.pi / 5) - math.pi / 2
        r = size if (i % 2 == 0) else size * 0.45
        points.append((center_x + r * math.cos(angle), center_y + r * math.sin(angle)))
    draw.polygon(points, fill=fill)

def get_posters():
    im18 = Image.open(THEME_18)
    p_sonic = im18.crop((340, 110, 700, 590)).resize((260, 310), Image.Resampling.LANCZOS)

    im10 = Image.open(THEME_10)
    p_venom = im10.crop((520, 80, 1020, 680)).resize((260, 310), Image.Resampling.LANCZOS)

    im6 = Image.open(THEME_6)
    p_mufasa = im6.crop((680, 260, 1340, 740)).resize((260, 310), Image.Resampling.LANCZOS)
    p_wick = im6.crop((235, 255, 505, 500)).resize((260, 310), Image.Resampling.LANCZOS)

    im12 = Image.open(THEME_12)
    p_culpa = im12.crop((350, 350, 640, 700)).resize((260, 310), Image.Resampling.LANCZOS)

    return {
        "sonic": p_sonic,
        "venom": p_venom,
        "mufasa": p_mufasa,
        "wick": p_wick,
        "culpa": p_culpa
    }

# =========================================================================
# 1. SCREENSHOT: HERO DASHBOARD (1920x1080)
# =========================================================================
def make_hero_dashboard():
    print("Generating screen_hero_dashboard.png...")
    # Use official Dreamania tech wallpaper
    src = Image.open(WALL_TECH).convert("RGB")
    src = src.resize((1920, 1080), Image.Resampling.LANCZOS)
    draw = ImageDraw.Draw(src)

    # Top overlay header banner
    top_overlay = Image.new("RGBA", (1920, 110), (7, 6, 14, 210))
    src.paste(top_overlay, (0, 0), top_overlay)

    # Brand Logo at Top-Left
    brand = Image.open(BRAND_LOGO_PATH).convert("RGBA")
    bh = 85
    bw = int(brand.width * (bh / brand.height))
    brand_res = brand.resize((bw, bh), Image.Resampling.LANCZOS)
    src.paste(brand_res, (40, 12), brand_res)

    # Expiry badge
    draw.rounded_rectangle([1340, 30, 1720, 76], radius=10, fill=(30, 27, 50, 220), outline=(139, 92, 246, 180), width=1)
    draw.text((1365, 42), "VIP 4K ULTRA  •  EXP: 31/12/2026", font=get_font(13, bold=True), fill=(196, 181, 253))

    # Digital Clock
    draw.text((1770, 38), "21:45", font=get_font(26, bold=True), fill=(245, 158, 11))

    # Bottom Dashboard Tiles
    bottom_bg = Image.new("RGBA", (1920, 280), (5, 4, 10, 245))
    src.paste(bottom_bg, (0, 800), bottom_bg)

    tiles = [
        {"tag": "LIVE TV", "title": "LIVE CHANNELS", "sub": "1,420 Streams", "focused": True},
        {"tag": "MOVIES", "title": "CINEMA VOD", "sub": "14,800+ Movies", "focused": False},
        {"tag": "SERIES", "title": "TV SHOWS", "sub": "4,200 Series", "focused": False},
        {"tag": "SPORTS", "title": "SPORTS HUB", "sub": "Live Match Center", "focused": False},
        {"tag": "PLAYLIST", "title": "PLAYLISTS", "sub": "Xtream & M3U", "focused": False},
        {"tag": "CONFIG", "title": "SETTINGS", "sub": "Themes & Engine", "focused": False},
    ]

    tile_w = 270
    tile_h = 180
    start_x = (1920 - (len(tiles) * (tile_w + 24) - 24)) // 2
    tile_y = 845

    for i, t in enumerate(tiles):
        tx = start_x + i * (tile_w + 24)
        ty = tile_y
        bbox = [tx, ty, tx + tile_w, ty + tile_h]

        if t["focused"]:
            draw.rounded_rectangle([tx-3, ty-3, tx+tile_w+3, ty+tile_h+3], radius=16, fill=(245, 158, 11))
            draw.rounded_rectangle(bbox, radius=14, fill=(139, 92, 246))

            # Focus Badge
            draw.rounded_rectangle([tx + 14, ty + 14, tx + 105, ty + 38], radius=6, fill=(245, 158, 11))
            draw.text((tx + 24, ty + 18), "FOCUSED", font=get_font(11, bold=True), fill=(0, 0, 0))

            # Icon tag pill
            draw.rounded_rectangle([tx + tile_w//2 - 40, ty + 50, tx + tile_w//2 + 40, ty + 80], radius=6, fill=(255, 255, 255, 50))
            draw.text((tx + tile_w//2, ty + 65), t["tag"], font=get_font(13, bold=True), fill=(255, 255, 255), anchor="mm")

            draw.text((tx + tile_w//2, ty + 118), t["title"], font=get_font(20, bold=True), fill=(255, 255, 255), anchor="mm")
            draw.text((tx + tile_w//2, ty + 148), t["sub"], font=get_font(13), fill=(254, 243, 199), anchor="mm")
        else:
            draw.rounded_rectangle(bbox, radius=14, fill=(20, 17, 34), outline=(45, 40, 68), width=1)

            draw.rounded_rectangle([tx + tile_w//2 - 38, ty + 48, tx + tile_w//2 + 38, ty + 76], radius=6, fill=(30, 26, 48))
            draw.text((tx + tile_w//2, ty + 62), t["tag"], font=get_font(12, bold=True), fill=(167, 139, 250), anchor="mm")

            draw.text((tx + tile_w//2, ty + 116), t["title"], font=get_font(18, bold=True), fill=(240, 240, 245), anchor="mm")
            draw.text((tx + tile_w//2, ty + 146), t["sub"], font=get_font(13), fill=(148, 163, 184), anchor="mm")

    src.save(os.path.join(SCREEN_DIR, "screen_hero_dashboard.png"), quality=95)
    print("Done screen_hero_dashboard.png")

# =========================================================================
# 2. SCREENSHOT: LIVE TV GUIDE (1920x1080)
# =========================================================================
def make_livetv_guide():
    print("Generating screen_livetv_guide.png...")
    img = Image.new("RGB", (1920, 1080), (10, 8, 20))
    draw = ImageDraw.Draw(img)

    for y in range(1080):
        r = int(10 + (y / 1080) * 12)
        g = int(8 + (y / 1080) * 8)
        b = int(20 + (y / 1080) * 32)
        draw.line([(0, y), (1920, y)], fill=(r, g, b))

    # Top Header
    draw.rectangle([0, 0, 1920, 90], fill=(13, 11, 24))
    draw.line([(0, 90), (1920, 90)], fill=(38, 33, 62), width=1)

    brand = Image.open(BRAND_LOGO_PATH).convert("RGBA")
    bh = 70
    bw = int(brand.width * (bh / brand.height))
    brand_res = brand.resize((bw, bh), Image.Resampling.LANCZOS)
    img.paste(brand_res, (36, 10), brand_res)

    draw.rounded_rectangle([320, 24, 420, 66], radius=8, fill=(28, 25, 48), outline=(60, 52, 92), width=1)
    draw.text((342, 34), "◀ BACK", font=get_font(14, bold=True), fill=(203, 213, 225))

    draw.text((445, 30), "LIVE CHANNELS", font=get_font(24, bold=True), fill=(255, 255, 255))
    draw.rounded_rectangle([665, 30, 800, 60], radius=15, fill=(139, 92, 246, 60), outline=(139, 92, 246), width=1)
    draw.text((680, 36), "1,420 Streams", font=get_font(12, bold=True), fill=(196, 181, 253))

    draw.rounded_rectangle([1360, 24, 1680, 66], radius=8, fill=(20, 17, 34), outline=(48, 42, 74), width=1)
    draw.text((1380, 34), "SEARCH: Channel name...", font=get_font(14), fill=(148, 163, 184))

    draw.rounded_rectangle([1700, 24, 1880, 66], radius=8, fill=(28, 25, 48), outline=(60, 52, 92), width=1)
    draw.text((1735, 34), "SORT: A-Z  ▼", font=get_font(13, bold=True), fill=(203, 213, 225))

    # Left Sidebar: Categories
    cat_x = 40
    cat_y = 115
    cat_w = 380

    categories = [
        {"name": "Favorites", "count": 14, "tag": "FAV", "active": True},
        {"name": "Sports UHD 4K", "count": 48, "tag": "4K", "active": False},
        {"name": "UK Entertainment", "count": 128, "tag": "UK", "active": False},
        {"name": "Cinema & Movies", "count": 164, "tag": "VOD", "active": False},
        {"name": "USA Premium TV", "count": 210, "tag": "US", "active": False},
        {"name": "News & Live 24/7", "count": 52, "tag": "LIVE", "active": False},
        {"name": "Kids & Family", "count": 42, "tag": "KIDS", "active": False},
        {"name": "Documentaries", "count": 65, "tag": "DOC", "active": False},
        {"name": "Music & Events", "count": 34, "tag": "MUSIC", "active": False},
    ]

    for i, c in enumerate(categories):
        cy = cat_y + i * 68
        bbox = [cat_x, cy, cat_x + cat_w, cy + 58]
        if c["active"]:
            draw.rounded_rectangle(bbox, radius=10, fill=(139, 92, 246), outline=(245, 158, 11), width=2)
            draw_heart(draw, cat_x + 35, cy + 29, size=18, fill=(239, 68, 68))
            draw.text((cat_x + 65, cy + 16), c["name"], font=get_font(18, bold=True), fill=(255, 255, 255))
            draw.rounded_rectangle([cat_x + cat_w - 65, cy + 14, cat_x + cat_w - 15, cy + 44], radius=6, fill=(245, 158, 11))
            draw.text((cat_x + cat_w - 40, cy + 20), str(c["count"]), font=get_font(13, bold=True), fill=(0, 0, 0), anchor="mm")
        else:
            draw.rounded_rectangle(bbox, radius=10, fill=(20, 17, 34), outline=(36, 32, 56), width=1)
            draw.ellipse([cat_x + 28, cy + 25, cat_x + 36, cy + 33], fill=(139, 92, 246))
            draw.text((cat_x + 55, cy + 16), c["name"], font=get_font(16, bold=False), fill=(226, 232, 240))
            draw.rounded_rectangle([cat_x + cat_w - 65, cy + 14, cat_x + cat_w - 15, cy + 44], radius=6, fill=(28, 25, 48))
            draw.text((cat_x + cat_w - 40, cy + 20), str(c["count"]), font=get_font(12), fill=(148, 163, 184), anchor="mm")

    # Right Content: Channels List
    ch_x = 450
    ch_y = 115
    ch_w = 1430

    channels = [
        {
            "num": "01",
            "name": "Sky Sports Premier League UHD",
            "epg": "Live: Arsenal vs Chelsea - Super Sunday Pre-Match & Kickoff",
            "quality": "4K 60FPS",
            "audio": "Dolby 5.1",
            "fav": True,
            "focused": True
        },
        {
            "num": "02",
            "name": "TNT Sports 1 Ultimate 4K",
            "epg": "Live: UEFA Champions League - Matchday Live Studio",
            "quality": "4K HDR",
            "audio": "Dolby 5.1",
            "fav": True,
            "focused": False
        },
        {
            "num": "03",
            "name": "HBO Max Cinema East HD",
            "epg": "Now Playing: Oppenheimer (2023) - Directed by Christopher Nolan",
            "quality": "1080p FHD",
            "audio": "Surround",
            "fav": True,
            "focused": False
        },
        {
            "num": "04",
            "name": "BBC One UHD Live",
            "epg": "Planet Earth III - Episode 6: Extreme Climates",
            "quality": "4K UHD",
            "audio": "Dolby",
            "fav": True,
            "focused": False
        },
        {
            "num": "05",
            "name": "beIN Sports 1 English HD",
            "epg": "Premier League World Review & Tactical Breakdown",
            "quality": "1080p 50FPS",
            "audio": "Stereo",
            "fav": True,
            "focused": False
        },
        {
            "num": "06",
            "name": "Canal+ Sport 4K Event",
            "epg": "Formula 1: Monaco Grand Prix - Main Race Live",
            "quality": "4K 60FPS",
            "audio": "Surround",
            "fav": True,
            "focused": False
        },
        {
            "num": "07",
            "name": "Sky Cinema Action FHD",
            "epg": "John Wick: Chapter 4 (2023) - Keanu Reeves",
            "quality": "1080p FHD",
            "audio": "Dolby 5.1",
            "fav": True,
            "focused": False
        }
    ]

    for i, ch in enumerate(channels):
        cy = ch_y + i * 105
        bbox = [ch_x, cy, ch_x + ch_w, cy + 92]

        if ch["focused"]:
            draw.rounded_rectangle([ch_x - 3, cy - 3, ch_x + ch_w + 3, cy + 95], radius=14, fill=(245, 158, 11))
            draw.rounded_rectangle(bbox, radius=12, fill=(26, 20, 48))

            draw.rounded_rectangle([ch_x + 18, cy + 18, ch_x + 72, cy + 74], radius=8, fill=(139, 92, 246))
            draw.text((ch_x + 45, cy + 34), ch["num"], font=get_font(20, bold=True), fill=(255, 255, 255), anchor="mm")

            draw.text((ch_x + 92, cy + 18), ch["name"], font=get_font(20, bold=True), fill=(255, 255, 255))
            draw.text((ch_x + 92, cy + 48), ch["epg"], font=get_font(14), fill=(253, 230, 138))

            draw.rounded_rectangle([ch_x + 1040, cy + 30, ch_x + 1145, cy + 62], radius=6, fill=(245, 158, 11))
            draw.text((ch_x + 1092, cy + 37), ch["quality"], font=get_font(12, bold=True), fill=(0, 0, 0), anchor="mm")

            draw.rounded_rectangle([ch_x + 1160, cy + 30, ch_x + 1265, cy + 62], radius=6, fill=(139, 92, 246, 120), outline=(139, 92, 246), width=1)
            draw.text((ch_x + 1212, cy + 37), ch["audio"], font=get_font(12, bold=True), fill=(255, 255, 255), anchor="mm")

            draw.rounded_rectangle([ch_x + 1285, cy + 24, ch_x + 1335, cy + 68], radius=8, fill=(239, 68, 68, 40), outline=(239, 68, 68), width=1)
            draw_heart(draw, ch_x + 1310, cy + 46, size=18, fill=(239, 68, 68))

            draw.rounded_rectangle([ch_x + 1350, cy + 24, ch_x + 1410, cy + 68], radius=8, fill=(245, 158, 11, 40), outline=(245, 158, 11), width=1)
            draw.text((ch_x + 1380, cy + 35), "MOVE", font=get_font(11, bold=True), fill=(245, 158, 11), anchor="mm")

        else:
            draw.rounded_rectangle(bbox, radius=12, fill=(19, 16, 32), outline=(36, 30, 56), width=1)

            draw.rounded_rectangle([ch_x + 18, cy + 18, ch_x + 72, cy + 74], radius=8, fill=(28, 25, 46))
            draw.text((ch_x + 45, cy + 34), ch["num"], font=get_font(18, bold=True), fill=(167, 139, 250), anchor="mm")

            draw.text((ch_x + 92, cy + 18), ch["name"], font=get_font(19, bold=True), fill=(241, 245, 249))
            draw.text((ch_x + 92, cy + 48), ch["epg"], font=get_font(13), fill=(148, 163, 184))

            draw.rounded_rectangle([ch_x + 1040, cy + 30, ch_x + 1145, cy + 62], radius=6, fill=(30, 27, 48), outline=(60, 52, 90), width=1)
            draw.text((ch_x + 1092, cy + 37), ch["quality"], font=get_font(12, bold=True), fill=(226, 232, 240), anchor="mm")

            draw.rounded_rectangle([ch_x + 1160, cy + 30, ch_x + 1265, cy + 62], radius=6, fill=(24, 21, 40), outline=(48, 42, 74), width=1)
            draw.text((ch_x + 1212, cy + 37), ch["audio"], font=get_font(12), fill=(148, 163, 184), anchor="mm")

            draw_heart(draw, ch_x + 1310, cy + 46, size=18, fill=(239, 68, 68))
            draw.text((ch_x + 1380, cy + 35), "▲▼", font=get_font(13), fill=(100, 116, 139), anchor="mm")

    # Bottom Remote Guide Banner
    draw.rectangle([0, 995, 1920, 1080], fill=(7, 6, 14))
    draw.line([(0, 995), (1920, 995)], fill=(32, 28, 52), width=1)

    shortcuts = [
        {"key": "OK", "desc": "Play Stream", "color": (139, 92, 246)},
        {"key": "RED BUTTON", "desc": "Toggle Favorite (Heart)", "color": (239, 68, 68)},
        {"key": "YELLOW BUTTON", "desc": "Move / Reorder Channel", "color": (245, 158, 11)},
        {"key": "BLUE BUTTON", "desc": "Quick Search", "color": (6, 182, 212)},
        {"key": "MENU", "desc": "Category List", "color": (148, 163, 184)}
    ]

    sx = 80
    for s in shortcuts:
        draw.rounded_rectangle([sx, 1020, sx + 135, 1058], radius=6, fill=(26, 22, 42), outline=s["color"], width=1)
        draw.text((sx + 67, 1030), s["key"], font=get_font(12, bold=True), fill=s["color"], anchor="mm")
        draw.text((sx + 150, 1030), s["desc"], font=get_font(14, bold=False), fill=(226, 232, 240), anchor="lm")
        sx += 360

    img.save(os.path.join(SCREEN_DIR, "screen_livetv_guide.png"), quality=95)
    print("Done screen_livetv_guide.png")

# =========================================================================
# 3. SCREENSHOT: ADVANCED 4K VIDEO PLAYER (1920x1080)
# =========================================================================
def make_player_osd():
    print("Generating screen_player_osd.png...")
    src = Image.open(WALL_SPORTS).convert("RGB")
    src = src.resize((1920, 1080), Image.Resampling.LANCZOS)
    draw = ImageDraw.Draw(src)

    draw.ellipse([700, 300, 1220, 750], outline=(255, 255, 255, 40), width=4)
    draw.line([(960, 200), (960, 850)], fill=(255, 255, 255, 40), width=4)

    # Top OSD Bar
    top_bar = Image.new("RGBA", (1920, 120), (7, 6, 15, 240))
    src.paste(top_bar, (0, 0), top_bar)

    # LIVE Red Badge
    draw.rounded_rectangle([40, 36, 140, 80], radius=8, fill=(220, 38, 38))
    draw.ellipse([54, 52, 66, 64], fill=(255, 255, 255))
    draw.text((75, 46), "LIVE", font=get_font(16, bold=True), fill=(255, 255, 255))

    draw.text((165, 34), "Sky Sports Main Event UHD", font=get_font(26, bold=True), fill=(255, 255, 255))
    draw.text((165, 72), "Premier League Live  •  Arsenal FC vs Chelsea FC  (Emirates Stadium - 2nd Half 74')", font=get_font(16), fill=(6, 182, 212))

    draw.rounded_rectangle([1380, 40, 1510, 80], radius=8, fill=(245, 158, 11))
    draw.text((1445, 50), "4K 60FPS", font=get_font(14, bold=True), fill=(0, 0, 0), anchor="mm")

    draw.rounded_rectangle([1525, 40, 1720, 80], radius=8, fill=(30, 27, 50), outline=(139, 92, 246), width=1)
    draw.text((1622, 50), "DOLBY ATMOS 5.1", font=get_font(13, bold=True), fill=(196, 181, 253), anchor="mm")

    draw.text((1770, 46), "21:45", font=get_font(26, bold=True), fill=(245, 158, 11))

    # Channel Quick Drawer on the left side
    drawer = Image.new("RGBA", (450, 720), (10, 8, 20, 240))
    src.paste(drawer, (40, 140), drawer)
    draw.rectangle([40, 140, 490, 860], outline=(48, 42, 74), width=1)

    draw.text((65, 165), "QUICK CHANNEL DRAWER", font=get_font(15, bold=True), fill=(139, 92, 246))

    drawer_ch = [
        {"num": "01", "name": "Sky Sports Main Event", "playing": True},
        {"num": "02", "name": "Sky Sports Premier League", "playing": False},
        {"num": "03", "name": "TNT Sports 1 4K", "playing": False},
        {"num": "04", "name": "TNT Sports 2 HD", "playing": False},
        {"num": "05", "name": "beIN Sports 1 English", "playing": False},
        {"num": "06", "name": "Eurosport 4K Live", "playing": False},
        {"num": "07", "name": "Canal+ Sport UHD", "playing": False},
    ]

    for i, d in enumerate(drawer_ch):
        dy = 210 + i * 86
        if d["playing"]:
            draw.rounded_rectangle([55, dy, 475, dy + 72], radius=10, fill=(139, 92, 246), outline=(245, 158, 11), width=2)
            draw.text((75, dy + 18), "▶ " + d["num"], font=get_font(16, bold=True), fill=(255, 255, 255))
            draw.text((125, dy + 18), d["name"], font=get_font(16, bold=True), fill=(255, 255, 255))
            draw.text((125, dy + 42), "PLAYING NOW", font=get_font(11, bold=True), fill=(254, 243, 199))
        else:
            draw.rounded_rectangle([55, dy, 475, dy + 72], radius=10, fill=(20, 17, 34), outline=(36, 32, 56), width=1)
            draw.text((75, dy + 24), d["num"], font=get_font(15, bold=True), fill=(148, 163, 184))
            draw.text((125, dy + 24), d["name"], font=get_font(15), fill=(226, 232, 240))

    # Bottom OSD Player Controls & Scrub Bar
    bot_bar = Image.new("RGBA", (1920, 200), (7, 6, 15, 245))
    src.paste(bot_bar, (0, 880), bot_bar)

    draw.rounded_rectangle([60, 905, 1860, 915], radius=5, fill=(35, 30, 55))
    draw.rounded_rectangle([60, 905, 1420, 915], radius=5, fill=(139, 92, 246))
    draw.ellipse([1412, 902, 1428, 918], fill=(245, 158, 11))

    draw.text((60, 928), "01:14:20", font=get_font(14, bold=True), fill=(196, 181, 253))
    draw.text((1805, 928), "-00:45:40", font=get_font(14, bold=True), fill=(148, 163, 184))

    controls = [
        {"label": "PREV", "active": False},
        {"label": "PAUSE", "active": True},
        {"label": "NEXT", "active": False},
        {"label": "FAVORITE (HEART)", "active": True},
        {"label": "AUDIO: English (5.1 Dolby) ▼", "active": True},
        {"label": "SUBTITLES: Off ▼", "active": False},
        {"label": "ASPECT: 16:9 Fit", "active": False},
        {"label": "CHANNELS DRAWER", "active": True},
    ]

    cx = 60
    for c in controls:
        w = len(c["label"]) * 10 + 26
        if c["active"]:
            draw.rounded_rectangle([cx, 960, cx + w, 1010], radius=8, fill=(139, 92, 246), outline=(245, 158, 11), width=1)
            draw.text((cx + w//2, 974), c["label"], font=get_font(12, bold=True), fill=(255, 255, 255), anchor="mm")
        else:
            draw.rounded_rectangle([cx, 960, cx + w, 1010], radius=8, fill=(24, 20, 40), outline=(48, 42, 74), width=1)
            draw.text((cx + w//2, 974), c["label"], font=get_font(12), fill=(203, 213, 225), anchor="mm")
        cx += w + 16

    src.save(os.path.join(SCREEN_DIR, "screen_player_osd.png"), quality=95)
    print("Done screen_player_osd.png")

# =========================================================================
# 4. SCREENSHOT: MOVIES & VOD POSTER WALL (1920x1080)
# =========================================================================
def make_vod_movies():
    print("Generating screen_vod_movies.png...")
    img = Image.new("RGB", (1920, 1080), (8, 7, 16))
    draw = ImageDraw.Draw(img)

    for y in range(1080):
        r = int(8 + (y / 1080) * 10)
        g = int(7 + (y / 1080) * 8)
        b = int(16 + (y / 1080) * 28)
        draw.line([(0, y), (1920, y)], fill=(r, g, b))

    # Top Header
    draw.rectangle([0, 0, 1920, 90], fill=(13, 11, 24))
    draw.line([(0, 90), (1920, 90)], fill=(38, 33, 62), width=1)

    brand = Image.open(BRAND_LOGO_PATH).convert("RGBA")
    bh = 70
    bw = int(brand.width * (bh / brand.height))
    brand_res = brand.resize((bw, bh), Image.Resampling.LANCZOS)
    img.paste(brand_res, (36, 10), brand_res)

    draw.rounded_rectangle([320, 24, 420, 66], radius=8, fill=(28, 25, 48), outline=(60, 52, 92), width=1)
    draw.text((342, 34), "◀ BACK", font=get_font(14, bold=True), fill=(203, 213, 225))

    draw.text((445, 30), "CINEMA VOD MOVIES", font=get_font(24, bold=True), fill=(255, 255, 255))
    draw.rounded_rectangle([720, 30, 875, 60], radius=15, fill=(6, 182, 212, 50), outline=(6, 182, 212), width=1)
    draw.text((735, 36), "14,800+ Titles", font=get_font(12, bold=True), fill=(165, 243, 252))

    vod_cats = ["TRENDING", "TOP RATED TMDB", "ACTION", "SCI-FI", "COMEDY", "NEW 2024-2025"]
    vcx = 40
    vcy = 110
    for i, vc in enumerate(vod_cats):
        w = len(vc) * 11 + 30
        if i == 0:
            draw.rounded_rectangle([vcx, vcy, vcx + w, vcy + 46], radius=20, fill=(139, 92, 246))
            draw.text((vcx + w//2, vcy + 15), vc, font=get_font(13, bold=True), fill=(255, 255, 255), anchor="mm")
        else:
            draw.rounded_rectangle([vcx, vcy, vcx + w, vcy + 46], radius=20, fill=(22, 18, 36), outline=(45, 38, 70), width=1)
            draw.text((vcx + w//2, vcy + 15), vc, font=get_font(13), fill=(203, 213, 225), anchor="mm")
        vcx += w + 16

    posters = get_posters()

    movies = [
        {"key": "sonic", "title": "SONIC 3: THE MOVIE", "year": "2024", "rating": "8.4", "genre": "Action / Adventure", "focused": True},
        {"key": "venom", "title": "VENOM: THE LAST DANCE", "year": "2024", "rating": "8.2", "genre": "Sci-Fi / Action", "focused": False},
        {"key": "mufasa", "title": "MUFASA: THE LION KING", "year": "2024", "rating": "8.5", "genre": "Adventure / Animation", "focused": False},
        {"key": "wick", "title": "JOHN WICK: CHAPTER 4", "year": "2023", "rating": "8.8", "genre": "Action / Thriller", "focused": False},
        {"key": "culpa", "title": "SUA CULPA (YOUR FAULT)", "year": "2024", "rating": "8.1", "genre": "Romance / Drama", "focused": False},
    ]

    card_w = 340
    card_h = 420
    start_x = 40
    card_y = 180

    for i, m in enumerate(movies):
        cx = start_x + i * (card_w + 26)
        bbox = [cx, card_y, cx + card_w, card_y + card_h]

        if m["focused"]:
            draw.rounded_rectangle([cx - 4, card_y - 4, cx + card_w + 4, card_y + card_h + 4], radius=18, fill=(245, 158, 11))
            draw.rounded_rectangle(bbox, radius=16, fill=(35, 22, 50))

            post_img = posters[m["key"]].resize((card_w - 20, 290), Image.Resampling.LANCZOS)
            img.paste(post_img, (cx + 10, card_y + 10))

            draw.text((cx + 16, card_y + 318), m["title"], font=get_font(16, bold=True), fill=(255, 255, 255))
            draw.text((cx + 16, card_y + 348), f"{m['year']}  •  {m['genre']}", font=get_font(13), fill=(253, 230, 138))

            draw_star(draw, cx + 28, card_y + 382, size=11, fill=(245, 158, 11))
            draw.text((cx + 46, card_y + 375), f"TMDb {m['rating']}", font=get_font(14, bold=True), fill=(245, 158, 11))

            draw.rounded_rectangle([cx + 200, card_y + 368, cx + card_w - 16, card_y + 402], radius=6, fill=(245, 158, 11))
            draw.text((cx + 260, card_y + 377), "▶ PLAY", font=get_font(12, bold=True), fill=(0, 0, 0), anchor="mm")
        else:
            draw.rounded_rectangle(bbox, radius=16, fill=(20, 17, 34), outline=(42, 36, 64), width=1)

            post_img = posters[m["key"]].resize((card_w - 20, 290), Image.Resampling.LANCZOS)
            img.paste(post_img, (cx + 10, card_y + 10))

            draw.text((cx + 16, card_y + 318), m["title"], font=get_font(15, bold=True), fill=(226, 232, 240))
            draw.text((cx + 16, card_y + 348), f"{m['year']}  •  {m['genre']}", font=get_font(12), fill=(148, 163, 184))

            draw_star(draw, cx + 28, card_y + 382, size=11, fill=(245, 158, 11))
            draw.text((cx + 46, card_y + 375), f"TMDb {m['rating']}", font=get_font(13, bold=True), fill=(245, 158, 11))

    # Bottom Movie Detail Spotlight
    spot_y = 630
    draw.rounded_rectangle([40, spot_y, 1880, 1040], radius=18, fill=(16, 13, 28), outline=(48, 42, 74), width=1)

    draw.text((70, spot_y + 30), "SONIC THE HEDGEHOG 3 (2024)", font=get_font(32, bold=True), fill=(255, 255, 255))
    draw.text((70, spot_y + 75), "★ 8.4/10 TMDb  •  1h 50m  •  4K UHD  •  Dolby Atmos  •  Action, Adventure, Family", font=get_font(16, bold=True), fill=(245, 158, 11))

    synopsis = "Sonic, Knuckles, and Tails reunite against a powerful new adversary, Shadow, a mysterious villain with powers unlike anything they have faced before. With their abilities outmatched in every way, Team Sonic must seek an unlikely alliance in hopes of stopping Shadow and protecting the planet."
    draw.text((70, spot_y + 115), synopsis, font=get_font(16), fill=(203, 213, 225))

    draw.rounded_rectangle([70, spot_y + 280, 270, spot_y + 345], radius=10, fill=(139, 92, 246), outline=(245, 158, 11), width=2)
    draw.text((170, spot_y + 300), "▶  WATCH MOVIE", font=get_font(16, bold=True), fill=(255, 255, 255), anchor="mm")

    draw.rounded_rectangle([295, spot_y + 280, 480, spot_y + 345], radius=10, fill=(26, 22, 44), outline=(60, 52, 92), width=1)
    draw.text((387, spot_y + 300), "TRAILER", font=get_font(15, bold=True), fill=(203, 213, 225), anchor="mm")

    draw.rounded_rectangle([505, spot_y + 280, 720, spot_y + 345], radius=10, fill=(26, 22, 44), outline=(239, 68, 68), width=1)
    draw_heart(draw, 540, spot_y + 312, size=15, fill=(239, 68, 68))
    draw.text((630, spot_y + 300), "ADD FAVORITE", font=get_font(14, bold=True), fill=(255, 255, 255), anchor="mm")

    img.save(os.path.join(SCREEN_DIR, "screen_vod_movies.png"), quality=95)
    print("Done screen_vod_movies.png")

# =========================================================================
# 5. SCREENSHOT: SERIES & EPISODES (1920x1080)
# =========================================================================
def make_series_detail():
    print("Generating screen_series_detail.png...")
    img = Image.new("RGB", (1920, 1080), (10, 8, 20))
    draw = ImageDraw.Draw(img)

    for y in range(1080):
        r = int(10 + (y / 1080) * 14)
        g = int(8 + (y / 1080) * 10)
        b = int(20 + (y / 1080) * 32)
        draw.line([(0, y), (1920, y)], fill=(r, g, b))

    # Top Header
    draw.rectangle([0, 0, 1920, 90], fill=(13, 11, 24))
    draw.line([(0, 90), (1920, 90)], fill=(38, 33, 62), width=1)

    brand = Image.open(BRAND_LOGO_PATH).convert("RGBA")
    bh = 70
    bw = int(brand.width * (bh / brand.height))
    brand_res = brand.resize((bw, bh), Image.Resampling.LANCZOS)
    img.paste(brand_res, (36, 10), brand_res)

    draw.rounded_rectangle([320, 24, 420, 66], radius=8, fill=(28, 25, 48), outline=(60, 52, 92), width=1)
    draw.text((342, 34), "◀ BACK", font=get_font(14, bold=True), fill=(203, 213, 225))

    draw.text((445, 30), "TV SERIES & EPISODES", font=get_font(24, bold=True), fill=(255, 255, 255))

    # Series Hero Spotlight
    draw.rounded_rectangle([40, 115, 1880, 480], radius=16, fill=(18, 15, 30), outline=(48, 42, 74), width=1)

    posters = get_posters()
    wick_img = posters["wick"].resize((250, 315), Image.Resampling.LANCZOS)
    img.paste(wick_img, (65, 140))
    draw.rectangle([65, 140, 315, 455], outline=(139, 92, 246), width=2)

    # Title & Stats
    draw.text((350, 145), "THE CONTINENTAL: FROM THE WORLD OF JOHN WICK", font=get_font(34, bold=True), fill=(255, 255, 255))
    draw.text((350, 198), "★ 8.6/10 TMDb  •  2023 - Present  •  1 Season  •  3 Feature Episodes  •  Action, Crime, Thriller", font=get_font(16, bold=True), fill=(245, 158, 11))

    plot = "The three-part event explores the origin behind the iconic hotel-for-assassins centerpiece of the John Wick universe through the eyes and actions of a young Winston Scott, as he's dragged into the Hell-scape of 1970's New York City to face a past he thought he'd left behind."
    draw.text((350, 240), plot, font=get_font(16), fill=(203, 213, 225))

    # Season selector tabs
    draw.rounded_rectangle([350, 380, 530, 435], radius=8, fill=(139, 92, 246), outline=(245, 158, 11), width=1)
    draw.text((440, 396), "SEASON 1 (3 Eps)", font=get_font(14, bold=True), fill=(255, 255, 255), anchor="mm")

    draw.rounded_rectangle([550, 380, 730, 435], radius=8, fill=(28, 24, 46), outline=(48, 42, 74), width=1)
    draw.text((640, 396), "SPECIALS (2)", font=get_font(14), fill=(203, 213, 225), anchor="mm")

    # Episodes Grid
    draw.text((40, 520), "EPISODES LIST - SEASON 1", font=get_font(22, bold=True), fill=(255, 255, 255))

    episodes = [
        {"num": "NIGHT 1", "title": "Brothers in Arms", "duration": "1h 26m", "resume": 75, "focused": True},
        {"num": "NIGHT 2", "title": "Loyalty to the Master", "duration": "1h 18m", "resume": 0, "focused": False},
        {"num": "NIGHT 3", "title": "Theater of Pain", "duration": "1h 32m", "resume": 0, "focused": False},
    ]

    ep_w = 586
    ep_h = 440
    start_x = 40
    ep_y = 570

    for i, ep in enumerate(episodes):
        ex = start_x + i * (ep_w + 30)
        bbox = [ex, ep_y, ex + ep_w, ep_y + ep_h]

        if ep["focused"]:
            draw.rounded_rectangle([ex - 3, ep_y - 3, ex + ep_w + 3, ep_y + ep_h + 3], radius=16, fill=(245, 158, 11))
            draw.rounded_rectangle(bbox, radius=14, fill=(28, 22, 46))

            draw.rounded_rectangle([ex + 12, ep_y + 12, ex + ep_w - 12, ep_y + 240], radius=10, fill=(60, 40, 90))
            draw.text((ex + ep_w//2, ep_y + 126), "▶ PLAY EPISODE", font=get_font(18, bold=True), fill=(255, 255, 255), anchor="mm")

            draw.rounded_rectangle([ex + 12, ep_y + 234, ex + ep_w - 12, ep_y + 240], radius=3, fill=(40, 30, 60))
            draw.rounded_rectangle([ex + 12, ep_y + 234, ex + 12 + int((ep_w - 24) * 0.75), ep_y + 240], radius=3, fill=(245, 158, 11))

            draw.text((ex + 20, ep_y + 265), ep["num"] + " • " + ep["title"], font=get_font(18, bold=True), fill=(255, 255, 255))
            draw.text((ex + 20, ep_y + 300), f"Duration: {ep['duration']}  •  Resume at 58:12", font=get_font(14), fill=(253, 230, 138))

            draw.rounded_rectangle([ex + 20, ep_y + 355, ex + ep_w - 20, ep_y + 415], radius=8, fill=(139, 92, 246))
            draw.text((ex + ep_w//2, ep_y + 375), "RESUME PLAYING", font=get_font(15, bold=True), fill=(255, 255, 255), anchor="mm")
        else:
            draw.rounded_rectangle(bbox, radius=14, fill=(18, 15, 30), outline=(40, 34, 60), width=1)
            draw.rounded_rectangle([ex + 12, ep_y + 12, ex + ep_w - 12, ep_y + 240], radius=10, fill=(30, 25, 45))
            draw.text((ex + ep_w//2, ep_y + 126), ep["num"], font=get_font(26, bold=True), fill=(148, 163, 184), anchor="mm")

            draw.text((ex + 20, ep_y + 265), ep["num"] + " • " + ep["title"], font=get_font(17, bold=True), fill=(226, 232, 240))
            draw.text((ex + 20, ep_y + 300), f"Duration: {ep['duration']}", font=get_font(14), fill=(148, 163, 184))

    img.save(os.path.join(SCREEN_DIR, "screen_series_detail.png"), quality=95)
    print("Done screen_series_detail.png")

# =========================================================================
# 6. SCREENSHOT: MULTI-THEME SHOWCASE (1920x1080)
# =========================================================================
def make_themes_showcase():
    print("Generating screen_themes_showcase.png...")
    img = Image.new("RGB", (1920, 1080), (8, 6, 16))
    draw = ImageDraw.Draw(img)

    for y in range(1080):
        r = int(8 + (y / 1080) * 12)
        g = int(6 + (y / 1080) * 8)
        b = int(16 + (y / 1080) * 28)
        draw.line([(0, y), (1920, y)], fill=(r, g, b))

    # Top Header
    draw.rectangle([0, 0, 1920, 90], fill=(13, 11, 24))
    draw.line([(0, 90), (1920, 90)], fill=(38, 33, 62), width=1)

    brand = Image.open(BRAND_LOGO_PATH).convert("RGBA")
    bh = 70
    bw = int(brand.width * (bh / brand.height))
    brand_res = brand.resize((bw, bh), Image.Resampling.LANCZOS)
    img.paste(brand_res, (36, 10), brand_res)

    draw.text((320, 30), "THEME & UI CUSTOMIZER", font=get_font(24, bold=True), fill=(255, 255, 255))
    draw.text((700, 36), "Tailor your Android TV experience with 4 modern handcrafted palettes", font=get_font(15), fill=(148, 163, 184))

    theme_cards = [
        {
            "name": "Dreamania Purple",
            "desc": "Signature violet & electric purple glow",
            "bg": (20, 15, 38),
            "accent": (139, 92, 246),
            "tag": "DEFAULT SIGNATURE",
            "active": True
        },
        {
            "name": "Cyber Neon",
            "desc": "High-contrast cyan & magenta neon aesthetics",
            "bg": (10, 25, 35),
            "accent": (6, 182, 212),
            "tag": "CYBERPUNK",
            "active": False
        },
        {
            "name": "Onyx Dark",
            "desc": "Ultra-deep slate dark mode tuned for OLED TVs",
            "bg": (15, 17, 23),
            "accent": (100, 116, 139),
            "tag": "OLED CINEMA",
            "active": False
        },
        {
            "name": "Royal Gold",
            "desc": "Warm luxury amber with metallic champagne finish",
            "bg": (30, 22, 10),
            "accent": (245, 158, 11),
            "tag": "LUXURY GOLD",
            "active": False
        }
    ]

    card_w = 425
    card_h = 880
    start_x = 55
    card_y = 130

    for i, t in enumerate(theme_cards):
        cx = start_x + i * (card_w + 35)
        bbox = [cx, card_y, cx + card_w, card_y + card_h]

        if t["active"]:
            draw.rounded_rectangle([cx - 4, card_y - 4, cx + card_w + 4, card_y + card_h + 4], radius=20, fill=(245, 158, 11))
            draw.rounded_rectangle(bbox, radius=18, fill=t["bg"])

            draw.rounded_rectangle([cx + card_w - 140, card_y + 24, cx + card_w - 24, card_y + 56], radius=8, fill=(245, 158, 11))
            draw.text((cx + card_w - 82, card_y + 34), "ACTIVE", font=get_font(12, bold=True), fill=(0, 0, 0), anchor="mm")
        else:
            draw.rounded_rectangle(bbox, radius=18, fill=t["bg"], outline=(50, 44, 75), width=1)

        draw.rounded_rectangle([cx + 24, card_y + 24, cx + 180, card_y + 56], radius=6, fill=t["accent"])
        draw.text((cx + 102, card_y + 34), t["tag"], font=get_font(12, bold=True), fill=(255, 255, 255), anchor="mm")

        draw.text((cx + 24, card_y + 80), t["name"], font=get_font(24, bold=True), fill=(255, 255, 255))
        draw.text((cx + 24, card_y + 115), t["desc"], font=get_font(13), fill=(203, 213, 225))

        prev_y = card_y + 160
        draw.rounded_rectangle([cx + 20, prev_y, cx + card_w - 20, prev_y + 600], radius=14, fill=(10, 8, 18), outline=t["accent"], width=2)

        draw.rectangle([cx + 20, prev_y, cx + card_w - 20, prev_y + 50], fill=(16, 13, 28))
        draw.text((cx + 40, prev_y + 18), "DREAMANIA PLAYER", font=get_font(14, bold=True), fill=t["accent"])

        mini_tiles = [
            ("LIVE TV", (60, 40, 90)),
            ("MOVIES", (40, 50, 80)),
            ("SERIES", (50, 40, 70)),
            ("SETTINGS", (30, 30, 50))
        ]
        for m_idx, (mt, m_col) in enumerate(mini_tiles):
            my = prev_y + 75 + m_idx * 125
            draw.rounded_rectangle([cx + 40, my, cx + card_w - 40, my + 105], radius=10, fill=m_col, outline=t["accent"], width=1)
            draw.text((cx + (card_w//2), my + 50), mt, font=get_font(18, bold=True), fill=(255, 255, 255), anchor="mm")

        btn_y = card_y + card_h - 75
        if t["active"]:
            draw.rounded_rectangle([cx + 24, btn_y, cx + card_w - 24, btn_y + 50], radius=10, fill=(245, 158, 11))
            draw.text((cx + card_w//2, btn_y + 18), "CURRENT THEME", font=get_font(14, bold=True), fill=(0, 0, 0), anchor="mm")
        else:
            draw.rounded_rectangle([cx + 24, btn_y, cx + card_w - 24, btn_y + 50], radius=10, fill=(35, 28, 54), outline=t["accent"], width=1)
            draw.text((cx + card_w//2, btn_y + 18), "APPLY THEME", font=get_font(14, bold=True), fill=(255, 255, 255), anchor="mm")

    img.save(os.path.join(SCREEN_DIR, "screen_themes_showcase.png"), quality=95)
    print("Done screen_themes_showcase.png")

if __name__ == "__main__":
    make_hero_dashboard()
    make_livetv_guide()
    make_player_osd()
    make_vod_movies()
    make_series_detail()
    make_themes_showcase()
    print("All 6 screenshots updated!")
