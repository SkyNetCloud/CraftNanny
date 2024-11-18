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
    term.clear()
    -- Draw header and footer
    draw_line_term(1, 1, 51, colors.lime)  -- Header
    draw_line_term(1, 19, 51, colors.lime) -- Footer
    draw_text_term(12, 1, 'CraftNanny Module Installer', colors.gray, colors.lime)
    draw_text_term(17, 19, 'craftnanny.org', colors.gray, colors.lime)
    -- Reset to black for the rest of the screen
    term.setBackgroundColor(colors.black)
end

-- Function to ask for version type (dev or prod)
-- Function to ask for version type (dev or prod)
function choose_version()
    term.clear()
    bars() -- Draw header/footer and clear the rest of the screen

    -- Middle section with black background
    draw_text_term(15, 3, 'Choose CraftNanny Version', colors.lime, colors.black)
    draw_text_term(1, 5, 'Which version would you like to install?', colors.white, colors.black)

    -- Options
    draw_text_term(2, 8, '1. Production', colors.white, colors.black)
    draw_text_term(2, 9, '2. Development', colors.white, colors.black)

    -- Input prompt
    draw_text_term(1, 13, 'Enter number:', colors.white, colors.black)
    term.setCursorPos(1, 14)
    term.setTextColor(colors.white)
    
    -- Read user input
    input = read()

    -- Handle user input
    if input == '1' then
        return "prod"
    elseif input == '2' then
        return "dev"
    else
        -- Invalid input handling
        draw_text_term(1, 15, 'Invalid input, try again.', colors.red, colors.black)
        sleep(1)
        return choose_version()  -- Recursively call the function if input is invalid
    end
end

-- Function to download the correct installer Lua file based on version (dev or prod)
local function download_installer(version)
    -- Adjust the URL to point to the subfolders based on version
    local url = string.format("https://dev.craftnanny.org/modules/%s/%s_installer.lua", version, version)
    print("Attempting to download from URL: " .. url)  -- Debugging line to check URL
    
    -- Make the request to the website
    local site, err = http.get(url)
    
    -- Check if the request was successful
    if not site then
        -- If the request failed, print the error and exit
        error("Failed to contact Site: " .. (err or "Unknown error"))
    end

    -- Read the content from the response
    local content = site.readAll()
    site.close()

    -- If content is nil or empty, something went wrong
    if not content or content == "" then
        error("Failed to download installer from " .. url)
    end

    -- Save the content to the specified file (downloaded installer)
    local installer_filename = string.format("%s_installer.lua", version)  -- Dynamic filename based on version
    local file = fs.open(installer_filename, "w")
    file.write(content)
    file.close()

    print("Downloaded the " .. version .. " installer successfully as " .. installer_filename)
end

-- Function to delete the current installer after the download
local function remove_installer()
    if fs.exists("installer.lua") then
        fs.delete("installer.lua")
        print("Removed installer.lua after download.")
    end
end

function run_installer()
    term.clear()
    bars()

    -- Directly prompt the user to choose a version
    local version = choose_version()  -- Ask for dev or prod

    -- Download the selected version's installer
    download_installer(version)       -- Download the corresponding installer
    sleep(0.5)

    -- Remove the initial installer file after the new one is downloaded
    remove_installer()

    -- Run the downloaded installer
    print("Running " .. version .. "_installer.lua...")  -- Debugging line to confirm execution
    shell.run(string.format("%s_installer.lua", version))
end

-- Main function to start the process
function start_installer()
    term.clear()
    run_installer()  -- Start the process of downloading and running the version-specific installer
end

-- Start the installer
start_installer()
