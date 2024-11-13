-- Auto-install setup with command-line arguments for a non-interactive mode
term.clear()
local token, module_name, username, type = '', '', '', ''
local githubRepo, branch, folder = "SkyNetCloud/CraftNanny", "master", "modules"
local updating = false
local args = {...}  -- Capture command-line arguments

local files = {
    player_module = "player.lua",
    redstone_module = "redstone.lua",
    fluid_module = "fluid.lua",
    energy_module = "energy.lua",
    hash_api = "sha1_api.lua",
    startup = "startup.lua"
}

-- UI Functions
function draw_text_term(x, y, text, text_color, bg_color)
  term.setTextColor(text_color)
  term.setBackgroundColor(bg_color)
  term.setCursorPos(x, y)
  write(text)
end

function draw_line_term(x, y, length, color)
    term.setBackgroundColor(color)
    term.setCursorPos(x, y)
    term.write(string.rep(" ", length))
end

function bars()
    draw_line_term(1, 1, 51, colors.lime)
    draw_line_term(1, 19, 51, colors.lime)
    draw_text_term(12, 1, 'CraftNanny Module Installer', colors.gray, colors.lime)
    draw_text_term(17, 19, 'craftnanny.org', colors.gray, colors.lime)
end

-- Config Functions
function save_config()
  local sw = fs.open("config.txt", "w")
  sw.writeLine(token)
  sw.writeLine(module_name)
  sw.writeLine(username)
  sw.writeLine(type)
  sw.close()
end

function load_config()
  local sr = fs.open("config.txt", "r")
  token = sr.readLine()
  module_name = sr.readLine()
  username = sr.readLine()
  type = sr.readLine()
  sr.close()
end

-- Hashing for Password
function hash(password)
    downloadFromGitHub(files.hash_api)
    os.loadAPI('sha1_api.lua')
    local response = http.post("https://craftnanny.org/code/salt.php", "user="..username)
    local salt = response.readAll()
    return sha1_api.sha1(salt..password)
end

-- Direct Module Installer
function install_module()
    local moduleFile = files[type .. "_module"]
    if not moduleFile then
        print("Invalid module type. Exiting installation.")
        return
    end

    term.clear()
    bars()
    draw_text_term(1, 3, 'Installing module...', colors.white, colors.black)
    sleep(0.5)

    -- Remove old versions
    if fs.exists("CN_module") then fs.delete("CN_module") end
    sleep(0.5)

    -- Fetch module from GitHub
    if not downloadFromGitHub(moduleFile) then
        print("Failed to download module file.")
        return
    end
    sleep(0.5)

    -- Fetch startup file
    if not downloadFromGitHub(files.startup) then
        print("Failed to download startup file.")
        return
    end

    -- Complete Installation
    draw_text_term(1, 13, 'Setup Complete', colors.lime, colors.black)
    if not updating then read() end
    launch_module()
end

-- Login and Registration Functions
function login()
    if not username or not token then
        print("Missing username or token for automated install.")
        return
    end
    install_module()
end

-- Module Selection
function select_module()
    if #args >= 3 then
        username = args[1]
        type = args[2]
        module_name = args[3]
        token = sha1_api.sha1(salt..password)  -- Replace "your_password" with a secure method or pre-supplied hashed token
        os.setComputerLabel(module_name:gsub("%s+", ""))
        login()
    else
        print("Usage: CraftNannyInstaller <username> <module_type> <module_name>")
    end
end

-- Start Installation
function start()
    if fs.exists("config.txt") then
        load_config()
        updating = true
        install_module()
    else
        select_module()
    end
end

start()
