#!/usr/bin/env python3
"""
podcast_to_chinese.py
將日文 Podcast 音訊轉成中文文字檔

用法：
  python podcast_to_chinese.py <MP3檔案或URL> [選項]

範例：
  python podcast_to_chinese.py episode.mp3
  python podcast_to_chinese.py https://rss.art19.com/episodes/xxx.mp3
  python podcast_to_chinese.py episode.mp3 --deepl-key YOUR_KEY
  python podcast_to_chinese.py episode.mp3 --model medium  (較快，但準確度略低)
"""

import os
import sys
import argparse
import re
import urllib.request
import urllib.parse
import xml.etree.ElementTree as ET
from pathlib import Path


# ──────────────────────────────────────────────
# 1. 依存套件檢查與安裝提示
# ──────────────────────────────────────────────

def check_dependencies():
    missing = []
    try:
        import whisper
    except ImportError:
        missing.append("openai-whisper")
    try:
        import torch
    except ImportError:
        missing.append("torch")
    if missing:
        print("❌ 缺少套件，請先執行：")
        print(f"   pip install {' '.join(missing)}")
        print()
        print("若使用 DeepL 翻譯，還需要：")
        print("   pip install deepl")
        sys.exit(1)


# ──────────────────────────────────────────────
# 2. RSS Feed 解析（可選：從 RSS 列出集數）
# ──────────────────────────────────────────────

def parse_rss(rss_url: str, max_episodes: int = 10) -> list[dict]:
    """從 RSS URL 取得最新幾集的標題與 MP3 連結"""
    print(f"📡 正在讀取 RSS Feed：{rss_url}")
    headers = {"User-Agent": "Mozilla/5.0 (podcast-to-chinese/1.0)"}
    req = urllib.request.Request(rss_url, headers=headers)
    with urllib.request.urlopen(req, timeout=30) as resp:
        xml_data = resp.read()

    root = ET.fromstring(xml_data)
    channel = root.find("channel")
    episodes = []

    for item in channel.findall("item")[:max_episodes]:
        title = item.findtext("title", "（無標題）").strip()
        # enclosure 標籤存放音訊 URL
        enclosure = item.find("enclosure")
        mp3_url = enclosure.get("url") if enclosure is not None else None
        pub_date = item.findtext("pubDate", "")
        episodes.append({"title": title, "url": mp3_url, "date": pub_date})

    return episodes


def select_episode(episodes: list[dict]) -> dict:
    """讓使用者從清單選擇集數"""
    print("\n📋 最新集數：")
    for i, ep in enumerate(episodes):
        date_str = ep["date"][:16] if ep["date"] else ""
        print(f"  [{i+1}] {date_str}  {ep['title']}")
    print()

    while True:
        choice = input(f"請輸入集數編號 (1-{len(episodes)})：").strip()
        if choice.isdigit() and 1 <= int(choice) <= len(episodes):
            return episodes[int(choice) - 1]
        print("  輸入無效，請重試。")


# ──────────────────────────────────────────────
# 3. 下載 MP3
# ──────────────────────────────────────────────

def download_mp3(url: str, output_path: str):
    """下載 MP3 並顯示進度"""
    print(f"⬇️  正在下載音訊...")
    print(f"   來源：{url[:80]}...")

    headers = {"User-Agent": "Mozilla/5.0 (podcast-to-chinese/1.0)"}
    req = urllib.request.Request(url, headers=headers)

    with urllib.request.urlopen(req, timeout=60) as resp:
        total = int(resp.headers.get("Content-Length", 0))
        downloaded = 0
        chunk_size = 1024 * 64  # 64KB

        with open(output_path, "wb") as f:
            while True:
                chunk = resp.read(chunk_size)
                if not chunk:
                    break
                f.write(chunk)
                downloaded += len(chunk)
                if total:
                    pct = downloaded / total * 100
                    mb = downloaded / 1024 / 1024
                    print(f"\r   {pct:.1f}%  ({mb:.1f} MB)", end="", flush=True)

    print(f"\n✅ 下載完成：{output_path}")


# ──────────────────────────────────────────────
# 4. 語音轉文字（Whisper）
# ──────────────────────────────────────────────

def transcribe_japanese(mp3_path: str, model_size: str = "large") -> str:
    """使用 Whisper 將日文音訊轉成文字"""
    import whisper

    print(f"\n🎙️  正在載入 Whisper 模型（{model_size}）...")
    print("   首次執行會自動下載模型，約需數分鐘，請耐心等待。")
    model = whisper.load_model(model_size)

    print(f"📝 正在轉錄日文音訊：{mp3_path}")
    print("   這可能需要幾分鐘，依音訊長度而定...")

    result = model.transcribe(
        mp3_path,
        language="ja",
        verbose=False,
        fp16=False,           # CPU 模式下設為 False
        condition_on_previous_text=True,
    )

    text = result["text"]
    print(f"✅ 日文轉錄完成（{len(text)} 字）")
    return text


# ──────────────────────────────────────────────
# 5. 翻譯（DeepL 或 GPT-like 提示）
# ──────────────────────────────────────────────

def translate_with_deepl(japanese_text: str, api_key: str) -> str:
    """使用 DeepL API 翻譯日文→繁體中文"""
    try:
        import deepl
    except ImportError:
        print("❌ 缺少 deepl 套件，請執行：pip install deepl")
        sys.exit(1)

    print("\n🌐 正在使用 DeepL 翻譯成中文...")

    translator = deepl.Translator(api_key)

    # 分段翻譯（DeepL 單次上限 128KB）
    MAX_CHUNK = 4000
    chunks = [japanese_text[i:i+MAX_CHUNK]
              for i in range(0, len(japanese_text), MAX_CHUNK)]

    translated_parts = []
    for idx, chunk in enumerate(chunks):
        if len(chunks) > 1:
            print(f"   翻譯中... ({idx+1}/{len(chunks)})", end="\r")
        result = translator.translate_text(chunk, target_lang="ZH")
        translated_parts.append(str(result))

    chinese_text = "".join(translated_parts)
    print(f"✅ 翻譯完成（{len(chinese_text)} 字）")
    return chinese_text


def translate_with_prompt(japanese_text: str) -> str:
    """
    無 API Key 時，產生提示讓使用者手動翻譯
    或可搭配其他翻譯工具
    """
    print("\n💡 未提供 DeepL API Key，跳過自動翻譯。")
    print("   你可以：")
    print("   1. 將 transcript_ja.txt 貼到 DeepL 網頁版翻譯")
    print("   2. 重新執行並加上 --deepl-key YOUR_KEY")
    print("   3. 將日文文字貼給 Claude 或 ChatGPT 翻譯")
    return ""


# ──────────────────────────────────────────────
# 6. 存檔與輸出
# ──────────────────────────────────────────────

def save_outputs(japanese: str, chinese: str, base_name: str):
    """儲存日文轉錄與中文翻譯"""
    ja_path = f"{base_name}_日文轉錄.txt"
    zh_path = f"{base_name}_中文翻譯.txt"
    combined_path = f"{base_name}_對照.txt"

    with open(ja_path, "w", encoding="utf-8") as f:
        f.write(japanese)
    print(f"💾 日文轉錄 → {ja_path}")

    if chinese:
        with open(zh_path, "w", encoding="utf-8") as f:
            f.write(chinese)
        print(f"💾 中文翻譯 → {zh_path}")

        # 額外產生對照版（每段日文/中文交錯）
        with open(combined_path, "w", encoding="utf-8") as f:
            f.write("═" * 60 + "\n")
            f.write("【日文原文】\n")
            f.write("═" * 60 + "\n\n")
            f.write(japanese + "\n\n")
            f.write("═" * 60 + "\n")
            f.write("【中文翻譯】\n")
            f.write("═" * 60 + "\n\n")
            f.write(chinese + "\n")
        print(f"💾 日中對照 → {combined_path}")


# ──────────────────────────────────────────────
# 7. 主程式
# ──────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(
        description="日文 Podcast → 中文文字檔",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__,
    )
    parser.add_argument(
        "source",
        nargs="?",
        help="MP3 檔案路徑、MP3 URL，或留空從 RSS 選擇",
    )
    parser.add_argument(
        "--rss",
        default="https://rss.art19.com/joi-ito",
        help="RSS Feed URL（預設：joi-ito）",
    )
    parser.add_argument(
        "--model",
        default="large",
        choices=["tiny", "base", "small", "medium", "large"],
        help="Whisper 模型大小（預設 large，最準確；tiny 最快）",
    )
    parser.add_argument(
        "--deepl-key",
        default=os.environ.get("DEEPL_API_KEY", ""),
        help="DeepL API Key（或設定環境變數 DEEPL_API_KEY）",
    )
    parser.add_argument(
        "--output",
        default="",
        help="輸出檔案前綴（預設使用集數標題）",
    )
    parser.add_argument(
        "--keep-mp3",
        action="store_true",
        help="保留下載的 MP3 檔案",
    )
    args = parser.parse_args()

    print("=" * 60)
    print("  🎙️  日文 Podcast → 中文文字檔  🇯🇵➡️🇹🇼")
    print("=" * 60)

    check_dependencies()

    mp3_path = None
    episode_title = "output"
    downloaded = False

    # ── 來源判斷 ──────────────────────────────
    if args.source and (args.source.startswith("http://") or
                        args.source.startswith("https://")):
        # 直接給 MP3 URL
        mp3_path = "downloaded_episode.mp3"
        download_mp3(args.source, mp3_path)
        downloaded = True
        episode_title = Path(args.source).stem[:50]

    elif args.source and os.path.isfile(args.source):
        # 本機 MP3 檔案
        mp3_path = args.source
        episode_title = Path(args.source).stem

    else:
        # 從 RSS 選集
        episodes = parse_rss(args.rss)
        if not episodes:
            print("❌ RSS Feed 中找不到任何集數")
            sys.exit(1)
        ep = select_episode(episodes)
        episode_title = re.sub(r'[\\/:*?"<>|]', "_", ep["title"])[:60]

        if not ep["url"]:
            print("❌ 該集沒有音訊連結")
            sys.exit(1)

        mp3_path = f"{episode_title}.mp3"
        download_mp3(ep["url"], mp3_path)
        downloaded = True

    # ── 輸出前綴 ─────────────────────────────
    base_name = args.output or episode_title

    # ── 轉錄 ─────────────────────────────────
    japanese_text = transcribe_japanese(mp3_path, model_size=args.model)

    # ── 翻譯 ─────────────────────────────────
    if args.deepl_key:
        chinese_text = translate_with_deepl(japanese_text, args.deepl_key)
    else:
        chinese_text = translate_with_prompt(japanese_text)

    # ── 儲存 ─────────────────────────────────
    print()
    save_outputs(japanese_text, chinese_text, base_name)

    # ── 清理暫存 MP3 ─────────────────────────
    if downloaded and not args.keep_mp3 and os.path.exists(mp3_path):
        os.remove(mp3_path)
        print(f"🗑️  已刪除暫存 MP3：{mp3_path}")

    print()
    print("🎉 完成！")


if __name__ == "__main__":
    main()

